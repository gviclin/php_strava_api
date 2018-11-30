<!doctype html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="http://openlayers.org/en/v3.16.0/css/ol.css" type="text/css">
    <style>
      .map {
        height: 680px;
        width: 100%;
      }
    </style>
    <script src="http://openlayers.org/en/v3.16.0/build/ol.js" type="text/javascript"></script>
    <title>OpenLayers 3 example</title>
  </head>
  <body>
    <h2>My Map</h2>
    <div id="map" class="map"></div>
    <script type="text/javascript">
		 
	   //Layer OSM
	   var layerOSMs = new ol.layer.Tile({
		source: new ol.source.OSM({
    attributions: [
      new ol.Attribution({
        html: 'All maps &copy; ' +
            '<a href="http://www.opencyclemap.org/">OpenCycleMap</a>'
      }),
      ol.source.OSM.ATTRIBUTION
    ],
    url: 'http://{a-c}.tile.opencyclemap.org/cycle/{z}/{x}/{y}.png'
  })
        });
		
	   //Layer GPX
	         var style = {
        'Point': new ol.style.Style({
          image: new ol.style.Circle({
            fill: new ol.style.Fill({
              color: 'rgba(255,255,0,0.4)'
            }),
            radius: 5,
            stroke: new ol.style.Stroke({
              color: '#ff0',
              width: 1
            })
          })
        }),
        'LineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: '#f00',
            width: 3
          })
        }),
        'MultiLineString': new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: '#0f0',
            width: 3
          })
        })
      };

      var vector1 = new ol.layer.Vector({
        source: new ol.source.Vector({
          url: 'gpx/Florent_Ligney_19-06-2013.gpx',
          //url: 'gpx/fells_loop.gpx',
          format: new ol.format.GPX()
        }),
        style: function(feature) {
          return style[feature.getGeometry().getType()];
        }
      });
	  
	   var vector2 = new ol.layer.Vector({
        source: new ol.source.Vector({
          url: 'gpx/Jean-Marc_LEMAIRE_18-05-2012.gpx',
          //url: 'gpx/fells_loop.gpx',
          format: new ol.format.GPX()
        }),
        style: function(feature) {
          return style[feature.getGeometry().getType()];
        }
      });
	   
	  
      var map = new ol.Map({
        target: 'map',
        layers: [layerOSMs, vector1, vector2],
        view: new ol.View({
          center: ol.proj.fromLonLat([5.3, 44.14]),
		  //center: [-7916041.528716288, 5228379.045749711],
          zoom: 13
        })
      });
    </script>
  </body>
</html>