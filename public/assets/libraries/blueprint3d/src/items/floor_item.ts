/// <reference path="../../lib/three.d.ts" />
/// <reference path="../model/model.ts" />
/// <reference path="item.ts" />
/// <reference path="metadata.ts" />

module BP3D.Items {
  /**
   * A Floor Item is an entity to be placed related to a floor.
   */
  export abstract class FloorItem extends Item {
    constructor(model: Model.Model, metadata: Metadata, geometry: THREE.Geometry, material: THREE.MeshFaceMaterial, position: THREE.Vector3, rotation: number, scale: THREE.Vector3) {
      super(model, metadata, geometry, material, position, rotation, scale);
    };

    /** */
    public placeInRoom() {
        if (!this.position_set) {
            var rooms = this.model.floorplan.getRooms();
            if(rooms.length > 0)
            {
                var center = rooms[0].getCenter3D();
                this.position.x = center.x;
                this.position.z = center.y;
                this.position.y = 0.5 * (this.geometry.boundingBox.max.y - this.geometry.boundingBox.min.y);
            }
        }
    };

    /** Take action after a resize */
    public resized() {
      this.position.y = this.halfSize.y;
    }

    /** */
    public moveToPosition(vec3, intersection) {
      // keeps the position in the room and on the floor
      if (!this.isValidPosition(vec3)) {
        this.showError(vec3);
        return;
      } else {
        this.hideError();
        vec3.y = this.position.y; // keep it on the floor!
        this.position.copy(vec3);
      }
    }

    /** */
    public isValidPosition(vec3): boolean {
      var corners = this.getCorners('x', 'z', vec3);

      // check if we are in a room
      var rooms = this.model.floorplan.getRooms();
      var isInARoom = false;
      var outside = true;
      var inObject = false;
      for (var i = 0; i < rooms.length; i++) {
        if (Core.Utils.pointInPolygon(vec3.x, vec3.z, rooms[i].interiorCorners) &&
          !Core.Utils.polygonPolygonIntersect(corners, rooms[i].interiorCorners)) {
          isInARoom = true;
          outside = false;
        }
      }

      // check if we are outside all other rooms
      if (this.obstructFloorMoves) {
          var rooms = this.model.floorplan.getRooms();
          for (var i = 0; i < rooms.length; i++) {
              if (!Core.Utils.polygonOutsidePolygon(corners, rooms[i].exteriorCorners,0,0) ||
                  Core.Utils.polygonPolygonIntersect(corners, rooms[i].exteriorCorners)) {
                  //console.log('object not outside other objects');
                  outside = false;
              }
              
          }
      }

      // check if we are inside all another object
      if (this.obstructFloorMoves) {
          var objects = this.model.scene.getItems();
          for (var i = 0; i < objects.length; i++) {
              if (objects[i] === this || !objects[i].obstructFloorMoves) {
                  continue;
              }
              if (Core.Utils.polygonInsidePolygon(corners, objects[i].getCorners('x', 'z', null),0,0) ||
                  Core.Utils.polygonPolygonIntersect(corners, objects[i].getCorners('x', 'z', null))) {
                  //console.log('object is in another object');
                  inObject = true;
              }
              if (Core.Utils.polygonInsidePolygon(objects[i].getCorners('x', 'z',null),corners,0,0) ||
                  Core.Utils.polygonPolygonIntersect(objects[i].getCorners('x', 'z',null),corners)) {
                  //console.log('object is in another object');
                  inObject = true;
              }
              
          }
      }
      console.log("In Object: "+inObject);
      if (inObject) {
          return false;
      }
      if (!isInARoom && !outside) {
        //console.log('object not in a room');
        return false;
      }

      return true;
    }
  }
}