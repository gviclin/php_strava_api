
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">

  
  

  
  
  

  

  <script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.js"></script>

  

  

  

  
    <link rel="stylesheet" type="text/css" href="/css/result-light.css">
  

  

  <style type="text/css">
    
  </style>

  <title>Backgrid ClientFilter example by yeoupooh</title>

  
    




<script type='text/javascript'>//<![CDATA[
$(function(){
var items = [
    {id:1, priority:1, type:3},
    {id:2, priority:2, type:2},
    {id:3, priority:3, type:1},
    {id:4, priority:4, type:2},
    {id:5, priority:5, type:2},
    {id:6, priority:6, type:3},
    {id:7, priority:7, type:2},
    {id:8, priority:8, type:1},
    {id:9, priority:9, type:3},
    {id:10, priority:10, type:2},
    {id:11, priority:11, type:2},
    {id:12, priority:12, type:1},
    {id:13, priority:13, type:3},
    {id:14, priority:14, type:4},
    {id:15, priority:15, type:2},
    {id:16, priority:16, type:3},
    {id:17, priority:17, type:2},
    {id:18, priority:18, type:1},
    {id:19, priority:19, type:1},
    {id:20, priority:20, type:2}
];
var collection = new Backbone.Collection(items);

var grid = new Backgrid.Grid({
    collection : collection,
    columns : [{
        name : 'priority',
        label : 'Priority',
        cell : Backgrid.NumberCell,
        sortable: true
    },{
        name : 'type',
        label : 'Type',
        cell : Backgrid.NumberCell,
        sortable : true
    }]
});


var GreaterThanClientFilter = Backgrid.Extension.ClientSideFilter.extend({  
  makeMatcher: function(query){
      var q = 1*query;
      return function (model) {
          if (isNaN(q)) return false;
          var keys = this.fields || model.keys();
          for (var i = 0, l = keys.length; i < l; i++) {
              value = model.get(keys[i]);
              if (!isNaN(value) &&  (1*value >= q))
                  return true;
            }
          return false;
      };
  }  
});

var filter = new GreaterThanClientFilter({
  collection: collection,
  placeholder: "Search"
});
                                         
$('#main').append( grid.render().el );

$("#client-side-filter").html(filter.render().el);
});//]]> 

</script>

  
</head>

<body>
  <script src="https://cdn.rawgit.com/jashkenas/underscore/1.5.2/underscore.js"></script>
<script src="https://cdn.rawgit.com/jashkenas/backbone/1.1.0/backbone.js"></script>
<script src="https://cdn.rawgit.com/wyuenho/backbone-pageable/master/lib/backbone-pageable.js"></script>
<script src="https://cdn.rawgit.com/wyuenho/backgrid/master/lib/backgrid.js"></script>
<script src="https://cdn.rawgit.com/wyuenho/backgrid-filter/master/backgrid-filter.js"></script>

<div id="client-side-filter"></div>
<div style="float:left">    
    <div id="main"></div>
</div>

  
</body>

</html>

