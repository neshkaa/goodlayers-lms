jQuery('.funfact-number').each(function() {
        var $this = jQuery(this);
        var parts = $this.text().match(/^(\d+)(.*)/);
        if (parts.length < 2) return;
      
        var scale = 20;
        var delay = 50;
        var end = 0+parts[1];
        var next = 0;
        var suffix = parts[2];
        
        var runUp = function() {
          var show = Math.ceil(next);
          $this.text(''+show+suffix);
          if (show == end) return;
          next = next + (end - next) / scale;
          window.setTimeout(runUp, delay);
        }
        runUp();
    });