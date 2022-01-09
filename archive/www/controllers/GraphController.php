<?php

class GraphController {

	static public function graphForce() {

		$json = $_GET['json'];
		if (!preg_match("/^\//",$json)) {
			return;
		}
		top3("Force Graph");

?>

<style>
.node {
}
.link {
  stroke: #999;
  stroke-opacity: .5;
}

</style>
<div><small>(NOTE: this is really early beta work, and I've probably messed it up, so just have fun. "DRAW" no conclusions (zing!)</small></div>
<div id="tipname">Please wait for data to load ... hover over circles for popup information. Drag to pin circles to a spot. Double-click to release.</div>
<script>

function mouseOverNode() {
	d = d3.select(this).data()[0];
	d3.select("#tipname").html(d.name);
}

function dblclick (d) {
  d3.select(this).classed("fixed", d.fixed = false);
}

function dragstart(d) {
	  d3.select(this).classed("fixed", d.fixed = true);

}

var width = 1400;
    height = 700;

var color = d3.scale.category20();

var force = d3.layout.force()
    .charge(-400)
    .size([width, height])
		.linkDistance(function(link) {
			return link.value*100;
		});


var svg = d3.select("body").append("svg")
    .attr("width", width)
    .attr("height", height);

d3.json("<?php print $json; ?>", function(error, graph) {
  if (error) throw error;

  force
      .nodes(graph.nodes)
      .links(graph.links)
      .start();

			var drag = force.drag()
			    .on("dragstart", dragstart);

  var link = svg.selectAll(".link")
      .data(graph.links)
    .enter().append("line")
      .attr("class", "link")
      .style("stroke-width", function(d) { return 2; });

  var node = svg.selectAll(".node")
      .data(graph.nodes)
    .enter().append("circle")
      .attr("class", "node")
      .attr("r", 10)
      .style("fill", function(d) { return color(d.group); })
      .call(force.drag)
			.on("mouseover",mouseOverNode)
			.on("dblclick", dblclick);


  node.append("title")
      .text(function(d) { return d.name; })

  force.on("tick", function() {
    link.attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });

    node.attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
  });



});

</script>



</body>
</html>
	<?php
		bottom3();
	}

}


