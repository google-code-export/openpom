/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

function selectall(object) {
  /*for (i=1; i<=num; i++)
    selectline(i);*/  
  $(object).parents('table').find('span.checkbox').each(function (index, element) {
    toggleCheckbox(element);
  });
}

function selectline(object) {
  /*elem = document.getElementById("check_" + i);
  if (elem == null) return;
  if (elem.checked == true)
    elem.checked = false;
  else
    elem.checked = true;*/
  toggleCheckbox($(object).find('span.checkbox').get(0));
}

function getallselectline(form) {
  
  /*
  var check = false ;
  for (i=1; i<=num; i++) {
    if (document.getElementById("check_" + i).checked == true) {
      var name = document.getElementById("check_" + i).name;
      var value = document.getElementById("check_" + i).value;
      var my_form = document.getElementById(form);
      var el = document.createElement('input');
      el.type = 'hidden'; 
      el.name = name;
      el.value = value;
      my_form.appendChild(el);
      check = true ;
    }
  }
  return check ;*/
}

function autorefresh() { 
  var refresh = $('#refreshspan');
  
  /* set next timeout */
  setTimeout(autorefresh, 1000);
  
  /* check if any line is checked */
  var line_is_checked = false;
  $('input[type="checkbox"].chkbox').each(function(index, element) {
    if (element.checked) {
      line_is_checked = true;
      return false;
    }
  });
  
  /* block countdown if line is checked or if filtering has focus */
  if (filtering_has_focus || line_is_checked) {
    if (refresh.css('text-decoration') != 'line-through') {
      refresh.css('text-decoration', 'line-through');
    }
    return;
  }
  
  mytime--;
  var my_get = location.href.replace(/^.*[\/#\\]/g, ''); 
  if (mytime == 0)
    window.location.href = my_get;
  if (refresh.css('text-decoration') == 'line-through') {
    refresh.css('text-decoration', 'none');
  }
  refresh.html(mytime);
}

function XMLHttpRequestSurcouche(zeasy) { 
  /* objet XMLHttpRequest */ 
  this.rq;
  var myrq;
  
  this.XMLHttpRequestResponse = function() { 
    if (myrq.readyState == 4 && myrq.status == 200) {
      if (it != null) {
        clearTimeout(it);
      }
      cache[zeasy] = myrq.responseText;
      if (current_data_displayed == zeasy) {
        it = setTimeout(hide_data, 5000);
        //popup.fadeOut(150, function () {
          popup.html(myrq.responseText).fadeIn(150); 
        //});
      }
    }
  };
  
  myrq = new XMLHttpRequest(); 
  myrq.onreadystatechange = this.XMLHttpRequestResponse;
  this.rq = myrq;
}

function get_data(type, id) {
  if (it != null) {
    clearTimeout(it);
  }
  
  popup.fadeOut(150, function () {
    current_data_displayed = type + ":" + id;
    
    if (typeof(cache[current_data_displayed]) != 'undefined') {
      it = setTimeout(hide_data, 5000);
      //popup.fadeOut(150, function () {
        popup.html(cache[current_data_displayed]).fadeIn(150);
      //});
      return;
      
    } else {
      var xhr = new XMLHttpRequestSurcouche(current_data_displayed);
      xhr.rq.open("GET", "status.php?type="+type+"&id="+id,  true);
      xhr.rq.send(null);
    }
  }); 
}

/*function WhereMouse(e) {
  var x;
  var y;
  if (e) {
    x = e.pageX + 5;
    y = e.pageY + 5;
    if (popup.clientWidth + x > window.innerWidth)
      x = x - popup.clientWidth - 10;
    if (popup.clientHeight - window.scrollY + y > window.innerHeight)
      y = y - popup.clientHeight - 10;
  }
  else {
    x = event.x + document.body.scrollLeft + 5;
    y = event.y + document.body.scrollTop + 5;
    if (popup.clientWidth + x > window.innerWidth)
      x = x - popup.clientWidth - 10;
    if (popup.clientHeight - document.body.scrollTop + y > window.innerHeight)
      y = y - popup.clientHeight - 10;
  }
  popup.style.left = document.body.clientWidth - 600 + "px";
  popup.style.top = document.body.scrollTop + 30 + "px";
}*/

function hide_data() {
  if (it != null) {
    clearTimeout(it);
  }
  popup.fadeOut(150);
}

/*function add_input(name, form) {
  var my_form = document.getElementById(form);
  var el = document.createElement('input');
  el.type = 'hidden';
  el.name = name;
  el.value = 1;
  my_form.appendChild(el);
}*/

function append_track(fobject) {
  fobject = $(fobject);
  if (!fobject.find('#track').length) {
    fobject.append('<input type="hidden" id="track" name="track" value="" />');
  }
}

function valid_ack(fobject) {
  var fobject = $(fobject);
  var com = fobject.find('#comment');
  
  if (com.length && com.val().length < 2) {
    com.focus();
    com.addClass('error');
    return false;
  } else {
    com.removeClass('error');
  }
  
  fobject.find('input.data').remove();
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      fobject.append($(element).find('input').clone());
    }
  });
  
  return true;
} 

function valid_down(fobject) {
  var fobject = $(fobject);
  var com = fobject.find('#comment');
  var start = fobject.find('#start');
  var end = fobject.find('#end');
  var hour = fobject.find('#hour');
  var minute = fobject.find('#minute');
  
  if (com.length && com.val().length < 2) {
    com.focus();
    com.addClass('error');
    return false;
  } else {
    com.removeClass('error');
  }
  
  if (start.length && start.val().length < 1
      && start.length && start.val().length < 1
      && end.length && end.val().length < 1
      && hour.length && hour.val().length < 1
      && minute.length && minute.val().length < 1) {
    hour.focus();
    start.addClass('error');
    end.addClass('error');
    hour.addClass('error');
    minute.addClass('error');
    return false;
  } else {
    start.removeClass('error');
    end.removeClass('error');
    hour.removeClass('error');
    minute.removeClass('error');
  }
  
  fobject.find('input.data').remove();
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      fobject.append($(element).find('input').clone());
    }
  });
  
  return true;
}

function valid_option(fobject) {
  return true;
}

function clicked_ack() {
  var selected = $('table#alert').find('span.checkbox span.checked');
  if (selected.length) { 
    popin('ack.php', { 
      onOpen: function() { filtering_has_focus = true; }, 
      onClosed: function() { filtering_has_focus = false; }
    });
  } else {
    blink_checkboxes();
  }
}

function clicked_generic_popin(href) {
  var selected = $('table#alert').find('span.checkbox span.checked');
  if (selected.length) { 
    popin(href, { 
      onOpen: function() { filtering_has_focus = true; }, 
      onClosed: function() { filtering_has_focus = false; }
    });
  } else {
    blink_checkboxes();
  }
}

function clicked_generic_action(action) {
  var selected = $('table#alert').find('span.checkbox span.checked');
  
  if (selected.length) { 
    var fobject = $('\
      <form action="" method="post">\
        <input type="hidden" name="' + action + '" value="" />\
      </form>\
    ');
    
    $('table#alert').find('span.checkbox').each(function (index, element) {
      if (testCheckbox(element)) {
        fobject.append($(element).find('input').clone());
      }
    });
    
    fobject.submit();
    
  } else {
    blink_checkboxes();
  }
}

function blink_checkboxes() {
  var all = $('table#alert').find('span.checkbox span');
  var btn = $('table#top').find('span.icon-ack');
  if (!accept_action) {
    return;
  } else {
    accept_action = false;
  }
  
  all.toggleClass('blink');
  setTimeout(function() {
    all.toggleClass('blink');
    setTimeout(function() {
      all.toggleClass('blink');
      setTimeout(function() {
        all.toggleClass('blink');
        accept_action = true;
      }, 120);
    }, 120);
  }, 120);
}

function pop(url, name, width, height) {
  window.open(url, name, 'location=no,toolbar=no,directories=no,menubar=no,resizable=no,scrollbars=yes,status=no,width='+width+',height='+height);
}

function popin(link, options) {
  if (typeof(options) == 'undefined') {
    options = {};
  }
  
  var settings = {
    initialWidth: "240px",
    initialHeight: "240px", 
    maxHeight: "95%", 
    maxWidth: "95%",
    returnFocus: false, 
    speed: 150, 
    scrolling: true, 
    href: link, 
    transition: 'elastic', 
    opacity: 0.8
  };
  
  for (o in options) {
    settings[o] = options[o];
  }
  
  try {
    $.colorbox(settings);
  } catch (error) {
    // do nothing
  }
}


function testCheckbox(object) {
  return $(object).find('span').hasClass('checked');
}

function toggleCheckbox(object) {
  $(object).find('span').toggleClass('checked');
}


