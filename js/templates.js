(function() {

  $.jmpress("template", "simple", {
    children: function(idx) {
      return {
        y: 0,
        x: idx * 1000,
        template: "simple-nested"
      };
    }
  });

  $.jmpress("template", "simple-nested", {
    children: function(idx) {
      return {
        y: 0,
        x: idx * 1000,
        scale: 0.3,
        template: "simple-nested"
      };
    }
  });

  $.jmpress("template", "helix", {
    children: function(idx) {
      return {
        y: 0,
        x: idx * 500,
        template: "helix-nested"
      };
    }
  });

  $.jmpress("template", "helix-nested", {
    children: function(idx) {
      return {
        y: 100,
        x: idx * 250,
        scale: 0.5,
        template: "helix-nested"
      };
    }
  });

  $.jmpress("template", "helix1", {
    children: function(idx, child) {
      return {
        scale: 0.5,
        phi: idx * 20,
        r: 700,
        rotate: idx * 20 - 90,
        template: "simple-nested"
      };
    }
  });

  $.jmpress("template", "helix2", {
    children: function(idx) {
      return {
        x: Math.cos(idx) * 300,
        y: Math.sin(idx) * 300,
        scale: 0.5,
        rotate: idx * 20 - 85
      };
    }
  });

  $.jmpress("template", "helix3", {
    children: function(idx) {
      return {
        y: 1000,
        x: idx * 1000,
        scale: 1 + idx * 0.2,
        rotate: -(idx * 20 % 360)
      };
    }
  });

  $(function() {
    var apply_transform, body, get_transform;
    if ($("body.template-shower").length) {
      body = document.body;
      get_transform = function() {
        var denominator;
        denominator = Math.max(body.clientWidth / window.innerWidth, body.clientHeight / window.innerHeight);
        return "scale(" + (1 / denominator) + ")";
      };
      apply_transform = function(transform) {
        var key, _i, _len, _ref;
        _ref = ["WebkitTransform", "MozTransform", "msTransform", "OTransform", "transform"];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          key = _ref[_i];
          body.style[key] = transform;
        }
      };
      $.jmpress("beforeInit", function(_, eventData) {
        var steps;
        steps = $(eventData.jmpress).find(eventData.settings.stepSelector);
        steps.first().addClass("first");
      });
      $.jmpress("afterInit", function(_, eventData) {
        apply_transform(get_transform());
      });
      return $.jmpress("beforeChange", function(element, eventData) {
        if ($(element).hasClass("first")) {
          $("html").addClass("first");
        } else {
          $("html").removeClass("first");
        }
        return console.log($("html").attr("class"));
      });
    }
  });

  $.jmpress("template", "shower", {
    children: function(idx) {
      if (idx === 0) {
        return {
          x: 0,
          y: 0,
          scale: 3
        };
      } else {
        return {
          x: ((idx - 1) % 2) * 1100 - 800,
          y: Math.floor((idx - 1) / 2) * 800 + 300,
          scale: 1
        };
      }
    }
  });

}).call(this);
