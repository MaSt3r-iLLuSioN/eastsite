/// <reference path="../../lib/three.d.ts" />

module BP3D.Three {
    export var Skybox = function (scene) {

        var scope = this;

        var scene = scene;

        var topColor = 0x999999;
        var bottomColor = 0x000000; //0xf9f9f9;//0x565e63
        var verticalOffset = 500
        var sphereRadius = 8000
        var widthSegments = 32
        var heightSegments = 15
        /*
        var vertexShaderOld = [
            "varying vec3 vWorldPosition;",
            "void main() {",
            "  vec4 worldPosition = modelMatrix * vec4( position, 1.0 );",
            "  vWorldPosition = worldPosition.xyz;",
            "  gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );",
            "}"
        ].join('\n');

        var fragmentShaderOld = [
            "uniform vec3 topColor;",
            "uniform vec3 bottomColor;",
            "uniform float offset;",
            "varying vec3 vWorldPosition;",
            "void main() {",
            "  float h = normalize( vWorldPosition + offset ).y;",
            "  gl_FragColor = vec4( mix( bottomColor, topColor, (h + 1.0) / 2.0), 1.0 );",
            "}"
        ].join('\n');
        */
        var skyVertex = [
            "varying vec2 vUV;",
            "void main() {",
            "   vUV = uv;",
            "   vec4 pos = vec4(position, 1.0);",
            "   gl_Position = projectionMatrix * modelViewMatrix * pos;",
            "}"
        ].join('\n');

        var skyFragment = [
            "uniform sampler2D texture;",
            "varying vec2 vUV;",
            "void main() {",
            "   vec4 sample = texture2D(texture, vUV);",
            "   gl_FragColor = vec4(sample.xyz, sample.w);",
            "}"
        ].join('\n');

        function init() {

            var uniforms = {
                texture: {
                    type: "t",
                    value: THREE.ImageUtils.loadTexture('assets/skyboxs/skydome.jpg')
                }   
                //topColor: {
                    //type: "c",
                    //value: new THREE.Color(topColor)
                //},
                //bottomColor: {
                    //type: "c",
                    //value: new THREE.Color(bottomColor)
                //},
                //offset: {
                    //type: "f",
                    //value: verticalOffset
                //}
            }

            var skyGeo = new THREE.SphereGeometry(
            sphereRadius, widthSegments, heightSegments);
            var skyMat = new THREE.ShaderMaterial({
                uniforms: uniforms,
                vertexShader: skyVertex,
                fragmentShader: skyFragment,
                side: THREE.BackSide
            });

            var sky = new THREE.Mesh(skyGeo, skyMat);
            scene.add(sky);
            //six sided skybox new improvement
            //var imagePrefix: string = "assets/skyboxs/Meadow/";
            //var directions: string[] = ["posx","negx","posy","negy","posz","negz"];
            //var imageSuffix: string = ".jpg";
            //var materialArray: THREE.MeshBasicMaterial[] = [];
            //for(var i = 0; i<6; i++)
            //{
                //materialArray.push(new THREE.MeshBasicMaterial({
                    //map: THREE.ImageUtils.loadTexture( imagePrefix + directions[i] + imageSuffix),
                    //side: THREE.BackSide
                //}));
            //}
            //var skyGeometry = new THREE.CubeGeometry( 10000, 10000, 10000, 1, 1, 1 );
            //var skyMaterial = new THREE.MeshFaceMaterial( materialArray );
            //var skyBox = new THREE.Mesh( skyGeometry, skyMaterial );
            //skyBox.position.y = 5000;
            //scene.add(skyBox);
            
        }

        init();
    }
}
