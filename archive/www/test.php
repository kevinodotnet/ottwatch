<html>
<head>
<script src="/jquery.js"></script>
<script src="springy/springy.js"></script>
<script src="springy/springyui.js"></script>
<style>

</style>
</head>
<body>
<script>

var graph = new Springy.Graph();
var nodes = new Array();

function addResults(data) {

  console.log('adding results');
  s = graph.newNode({label: 'TheSearch'});

  data.matches.forEach(function(m) {
    // console.log(m);
    console.log(m.type + ' ' + m.id);
    // var node = graph.newNode({label: m.type + ' ' + m.id});
    var node = graph.newNode({label: m.desc});
    nodes[m.uid] = node;
    // graph.newEdge(searchQ, node);
  });
  data.matches.forEach(function(m) {
    node = nodes[m.uid];
    graph.newEdge(s, node);
  });
  var springy = window.springy = jQuery('#springydemo').springy({
    graph: graph,
    nodeSelected: function(node){
      console.log('Node selected: ' + JSON.stringify(node));
    }
  });

  console.log('done');
}

function search(qval) {
  $.get( '/api/search', {q: qval}, function( data ) {
    addResults(data);
  },'json')
  .always(function() {
    console.log('search done');
  });
}

search('<?php print $_GET['q']; ?>');

/*
var dennis = graph.newNode({
  label: 'Dennis',
  ondoubleclick: function() { console.log("Hello!"); }
});
var michael = graph.newNode({foo: 'test',label: 'Michael'});
var jessica = graph.newNode({label: 'Jessica'});
var timothy = graph.newNode({label: 'Timothy'});
var barbara = graph.newNode({label: 'Barbara'});
var franklin = graph.newNode({label: 'Franklin'});
var monty = graph.newNode({label: 'Monty'});
var james = graph.newNode({label: 'James'});
var bianca = graph.newNode({label: 'Bianca'});

graph.newEdge(dennis, michael, {color: '#00A0B0'});
graph.newEdge(michael, dennis, {color: '#6A4A3C'});
graph.newEdge(michael, jessica, {color: '#CC333F'});
graph.newEdge(jessica, barbara, {color: '#EB6841'});
graph.newEdge(michael, timothy, {color: '#EDC951'});
graph.newEdge(franklin, monty, {color: '#7DBE3C'});
graph.newEdge(dennis, monty, {color: '#000000'});
graph.newEdge(monty, james, {color: '#00A0B0'});
graph.newEdge(barbara, timothy, {color: '#6A4A3C'});
graph.newEdge(dennis, bianca, {color: '#CC333F'});
graph.newEdge(bianca, monty, {color: '#EB6841'});
*/

</script>

<canvas id="springydemo" width="1200" height="800" style="border: 1px solid #000000;"></canvas>
</body>
</html>
