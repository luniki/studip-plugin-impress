$.jmpress "template", "simple",
  children: (idx) ->
    y: 0
    x: idx * 1000
    template: "simple-nested"

# TODO funktioniert nicht
$.jmpress "template", "simple-nested",
  children: (idx) ->
    y: 0
    x: idx * 1000
    scale: 0.3
    template: "simple-nested"

$.jmpress "template", "helix",
  children: (idx) ->
    y: 0
    x: idx * 500
    template: "helix-nested"

$.jmpress "template", "helix-nested",
  children: (idx) ->
    y: 100
    x: idx * 250
    scale: 0.5
    template: "helix-nested"

$.jmpress "template", "helix1",
  children: (idx, child) ->
    scale: 0.5
    phi: idx * 20
    r: 700
    rotate: idx * 20 - 90
    template: "simple-nested"

$.jmpress "template", "helix2",
  children: (idx) ->
    x: Math.cos(idx) * 300
    y: Math.sin(idx) * 300
    scale: 0.5
    rotate: idx * 20 - 85

$.jmpress "template", "helix3",
  children: (idx) ->
    y: 1000
    x: idx * 1000
    scale: 1 + idx * 0.2
    rotate: - (idx * 20 % 360)

$ ->
  if $("body.template-shower").length

    body = document.body

    get_transform = ->
      denominator = Math.max(
        body.clientWidth / window.innerWidth,
        body.clientHeight / window.innerHeight
        )
      "scale(#{1 / denominator})"

    apply_transform = (transform) ->
      for key in ["WebkitTransform", "MozTransform", "msTransform", "OTransform", "transform"]
        body.style[key] = transform
      return

    $.jmpress "beforeInit", (_, eventData) ->
      steps = $(eventData.jmpress).find(eventData.settings.stepSelector)
      steps.first().addClass "first"

      #steps.on 'click', -> console.log
      return

    $.jmpress "afterInit", (_, eventData) ->
      apply_transform do get_transform
      return

    $.jmpress "beforeChange", (element, eventData) ->
      if $(element).hasClass "first"
        $("html").addClass "first"
      else
        $("html").removeClass "first"
      console.log $("html").attr("class")


$.jmpress "template", "shower",
  children: (idx) ->

    if idx is 0
      x: 0
      y: 0
      scale: 3
    else
      x: ((idx - 1) % 2) * 1100 - 800
      y: Math.floor((idx - 1) / 2) * 800 + 300
      scale: 1
