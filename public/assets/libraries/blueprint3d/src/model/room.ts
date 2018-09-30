/// <reference path="../../lib/three.d.ts" />
/// <reference path="../../lib/jQuery.d.ts" />
/// <reference path="../core/utils.ts" />
/// <reference path="corner.ts" />
/// <reference path="floorplan.ts" />
/// <reference path="half_edge.ts" />

/*
TODO
var Vec2 = require('vec2')
var segseg = require('segseg')
var Polygon = require('polygon')
*/

module BP3D.Model {

  /** Default texture to be used if nothing is provided. */
  const defaultRoomTexture = {
    url: "rooms/textures/hardwood.png",
    scale: 400
  }

  /** 
   * A Room is the combination of a Floorplan with a floor plane. 
   */
  export class Room {

    /** */
    public interiorCorners: Corner[] = [];
    
    public exteriorCorners: Corner[] = [];

    /** */
    private edgePointer = null;

    /** floor plane for intersection testing */
    public floorPlane: THREE.Mesh = null;

    public name: string = '';
    
    public biggestWall = null;
    public smallestWall = null;
    public sqft: number = 0;
    public wallCount: number = 0;
    public walls: Wall[] = [];

    public cmPerPixel: number;
    public pixelsPerCm: number;

    /** */
    private customTexture = false;

    /** */
    private floorChangeCallbacks = $.Callbacks();
    /**
     *  ordered CCW
     */
    constructor(public floorplan: Floorplan, public corners: Corner[]) {
      this.updateWalls();
      this.updateInteriorCorners();
      this.updateExteriorCorners();
      this.generatePlane();
      var cmPerFoot = 30.48;
      var pixelsPerFoot = 15.0;
      this.cmPerPixel = cmPerFoot * (1.0 / pixelsPerFoot);
      this.pixelsPerCm = 1.0 / this.cmPerPixel;
    }

    public getUuid(): string {
      var cornerUuids = Core.Utils.map(this.corners, function (c) {
        return c.id;
      });
      cornerUuids.sort();
      return cornerUuids.join();
    }

    public fireOnFloorChange(callback) {
      this.floorChangeCallbacks.add(callback);
    }

    private getTexture() {
      var uuid = this.getUuid();
      var tex = this.floorplan.getFloorTexture(uuid);
      return tex || defaultRoomTexture;
    }

    /** 
     * textureStretch always true, just an argument for consistency with walls
     */
    private setTexture(textureUrl: string, textureStretch, textureScale: number) {
      var uuid = this.getUuid();
      this.floorplan.setFloorTexture(uuid, textureUrl, textureScale);
      this.floorChangeCallbacks.fire();
    }

    private generatePlane() {
      var points = [];
      this.interiorCorners.forEach((corner) => {
        points.push(new THREE.Vector2(
          corner.x,
          corner.y));
      });
      var shape = new THREE.Shape(points);
      var geometry = new THREE.ShapeGeometry(shape);
      this.floorPlane = new THREE.Mesh(geometry,
        new THREE.MeshBasicMaterial({
          side: THREE.DoubleSide
        }));
      this.floorPlane.visible = false;
      this.floorPlane.rotation.set(Math.PI / 2, 0, 0);
      (<any>this.floorPlane).room = this; // js monkey patch
    }

    private cycleIndex(index) {
      if (index < 0) {
        return index += this.corners.length;
      } else {
        return index % this.corners.length;
      }
    }

    private updateInteriorCorners() {
      var edge = this.edgePointer;
      while (true) {
        this.interiorCorners.push(edge.interiorStart());
        edge.generatePlane();
        if (edge.next === this.edgePointer) {
          break;
        } else {
          edge = edge.next;
        }
      }
    }
    
    private updateExteriorCorners()
    {
        var edge = this.edgePointer;
        while (true) {
            this.exteriorCorners.push(edge.exteriorStart());
            if(edge.next === this.edgePointer) {
                break;
            } else {
                edge = edge.next;
            }
        }
    }
    
    public getCenter3D() {
        var X:number[] = [];
        var Y:number[] =[];
        this.interiorCorners.forEach(function(corner){
            X.push(corner.x);
        });
        
        this.interiorCorners.forEach(function(corner){
            Y.push(corner.y);
        });
        
        var points: THREE.Vector2[] = [];
        for(var i = 0; i < X.length; i++)
        {
            var coord = new THREE.Vector2(0,0);
            coord.x = X[i];
            coord.y = Y[i];
            points.push(coord);
        }
        var centroid = {x: 0, y: 0};
        for(var i = 0; i < points.length; i++) {
           var point = points[i];
           centroid.x += point.x;
           centroid.y += point.y;
        }
        centroid.x /= points.length;
        centroid.y /= points.length;
        return centroid;
    }
    
    /** 
     * Populates each wall's half edge relating to this room
     * this creates a fancy doubly connected edge list (DCEL)
     */
    private updateWalls() {

      var prevEdge = null;
      var firstEdge = null;

      for (var i = 0; i < this.corners.length; i++) {

        var firstCorner = this.corners[i];
        var secondCorner = this.corners[(i + 1) % this.corners.length];

        // find if wall is heading in that direction
        var wallTo = firstCorner.wallTo(secondCorner);
        var wallFrom = firstCorner.wallFrom(secondCorner);

        if (wallTo) {
          var edge = new HalfEdge(this, wallTo, true);
        } else if (wallFrom) {
          var edge = new HalfEdge(this, wallFrom, false);
        } else {
          // something horrible has happened
          console.log("corners arent connected by a wall, uh oh");
        }

        if (i == 0) {
          firstEdge = edge;
        } else {
          edge.prev = prevEdge;
          prevEdge.next = edge;
          if (i + 1 == this.corners.length) {
            firstEdge.prev = edge;
            edge.next = firstEdge;
          }
        }
        prevEdge = edge;
      }

      // hold on to an edge reference
      this.edgePointer = firstEdge;
    }
  }
}
