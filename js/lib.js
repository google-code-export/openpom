/*
  OpenPOM

  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
*/

/*******************************************************************************
 * Status popin
 * - declare javascript event handlers
 * - FIXME: initialize popin-related globals here instead of header.php
 ******************************************************************************/

function set_popin(url)
{
    popin_url = url;

    if (popin_flag) {
        popin_timer_show = setTimeout(function () {
            show_popin();
        }, 500);
    }
}

function unset_popin()
{
    popin_url = null;

    if (popin_timer_show != null) {
        clearTimeout(popin_timer_show);
        popin_timer_show = null;
    }
}

function resize_popin(wpx)
{
    var width;

    if (typeof(wpx) != 'undefined' && !isNaN(wpx) && wpx > 100)
        width = wpx + 'px';
    else if (typeof(popin_init_width) != 'undefined' && !isNaN(popin_init_width))
        width = popin_init_width + 'px';
    else
        width = 'auto';

    popin_jobject.css('width', width);
}

function show_popin()
{
    if (popin_timer_show != null) {
        clearTimeout(popin_timer_show);
        popin_timer_show = null;
    }

    if (popin_timer_hide != null) {
        clearTimeout(popin_timer_hide);
        popin_timer_hide = null;
    }

    if (popin_url == null)
        return;

    popin_jobject.fadeOut(150, function () {
        if (popin_url in popin_cache) {
            popin_timer_hide = setTimeout(hide_popin, 5000);
            popin_jobject.html(popin_cache[popin_url]);
            resize_popin();
            popin_jobject.fadeIn(150);
        }
        else {
            $.ajax({
                dataType: 'html',
                async: true,
                timeout: 10000,
                type: 'GET',
                url: popin_url,
                success: function (data, textStatus, jqXHR) {
                    popin_cache[this.url] = data;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    popin_cache[this.url] = 'An error has occured';
                },
                complete: function (jqXHR, textStatus) {
                    popin_timer_hide = setTimeout(hide_popin, 5000);
                    popin_jobject.html(popin_cache[this.url]);
                    resize_popin();
                    popin_jobject.fadeIn(150);
                }
            });
        }
    });
}

function hide_popin()
{
    if (popin_timer_hide != null) {
        clearTimeout(popin_timer_hide);
        popin_timer_hide = null;
    }

    popin_jobject.fadeOut(150);
}

function restart_popin_hide_timer()
{
    if (popin_timer_hide == null)
        return;

    clearTimeout(popin_timer_hide);
    popin_timer_hide = setTimeout(hide_popin, 5000);
}

/* Attach events related to the status popin */

$(document).keydown(function (event) {
    if (event.which != 17 || popin_keydown)
        return;

    popin_keydown = true;
    popin_flag = popin_flag ? false : true;

    if (popin_flag)
        show_popin();
});

$(document).keyup(function (event) {
    if (event.which != 17)
        return;

    popin_keydown = false;
    popin_flag = popin_flag ? false : true;

    if (popin_flag)
        show_popin();
});

$(document).click(function (event) {
    hide_popin();
});

$(document).ready(function () {
    /* put the status popin at right place */
    var first_alert_item = $('table#alert tr.alert-item:first');

    if (first_alert_item.length)
        popin_jobject.css('top', (first_alert_item.offset().top - 10) + 'px');

    /* add the status popin to document */
    $('body').append(popin_jobject);
    $('input#top-dummy-focus').focus();
});

/*******************************************************************************
 * FUNCTIONS
 ******************************************************************************/

function regexp_escape(input) {
  return input.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
}

function selectall(object) {
  /*for (i=1; i<=num; i++)
    selectline(i);*/
  $(object).parents('table').find('span.checkbox').each(function (index, element) {
    if (index > 0) {
      toggleCheckbox(element);
      $(element).parents('tr').toggleClass('selected');
    }
  });
}

function selectline(object, event) {
  /* try to not trigger event if the source */
  /* element or its parent is a link */
  if (!event) {
    event = window.event;
  }

  var target = null;
  if (event.target) {
    target = event.target;
  } else if (event.srcElement) {
    target = event.srcElement;
  }

  if (target != null) {
    if (target.nodeName.toLowerCase() == 'a') {
      return;
    }
    var parent = $(target).parent();
    if (parent.length) {
      if (parent.get(0).nodeName.toLowerCase() == 'a') {
        return;
      }
    }
  }

  /* eventually, trigger the action */
  var chk = $(object).find('span.checkbox');
  if (!chk.length) {
    return;
  }
  chk = chk.get(0);

  if (typeof(event.shiftKey) != 'undefined'
      && event.shiftKey === true
      && lastChecked != null) {

    var allChk = $('span.checkbox');
    var iClicked = allChk.index(chk);
    var iPrev = allChk.index(lastChecked);
    var newState = !testCheckbox(allChk[iClicked]);

    for (var i=Math.min(iClicked,iPrev); i<=Math.max(iClicked,iPrev); i++) {
      checkCheckbox(allChk[i], newState)
      if (newState) {
          $(allChk[i]).parents('tr').addClass('selected');
      } else {
          $(allChk[i]).parents('tr').removeClass('selected');
      }
    }

  } else {
    toggleCheckbox(chk);
    $(object).toggleClass('selected');
  }

  /* try to remove potential text selection */
  if (window.getSelection) {
    window.getSelection().removeAllRanges();
  } else if (document.selection && document.selection.empty) {
    document.selection.empty();
  }

  /* keep trace of last checked */
  lastChecked = chk;
}

function autorefresh() {
  var refresh = $('#refresh-countdown');
  var line_is_checked = false;
  var keep_going = true;

  /* check if any line is checked */
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      line_is_checked = true;
      return false; // this only breaks from the "each" loop
    }
  });

  /* block countdown if line is checked or if filtering has focus */
  if (filtering_has_focus || line_is_checked) {
    if (refresh.css('text-decoration') != 'line-through') {
      refresh.css('text-decoration', 'line-through');
    }

  /* otherwise decrement countdown */
  } else {
    if (refresh.css('text-decoration') == 'line-through') {
      refresh.css('text-decoration', 'none');
    }

    refresh.html(--mytime);
    if (mytime <= 0) {
      keep_going = false;
      window.location.href = window.location.href;
    }
  }

  /* set next timeout */
  if (keep_going) {
    window.setTimeout(autorefresh, 1000);
  }
}

function append_track(fobject) {
  fobject = $(fobject);
  if (!fobject.find('#track').length) {
    fobject.append('<input type="hidden" id="track" name="track" value="1" />');
  }
}

function valid_ack(fobject, illegal) {
  var fobject = $(fobject);
  var com = fobject.find('#comment');

  com.removeClass('error');
  illegal = decodeURIComponent(illegal);
  var regexp = new RegExp('^[^' + regexp_escape(illegal) + ']{2,64}$');

  if (com.length && !regexp.test(com.val())) {
    com.focus();
    com.addClass('error');
    return false;
  }

  fobject.find('input.data').remove();
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      fobject.append($(element).find('input').clone());
    }
  });

  return true;
}

function valid_down(fobject, illegal) {
  var fobject = $(fobject);
  var com = fobject.find('#comment');
  var start = fobject.find('#start');
  var end = fobject.find('#end');
  var hour = fobject.find('#hour');
  var minute = fobject.find('#minute');

  start.removeClass('error');
  end.removeClass('error');
  hour.removeClass('error');
  minute.removeClass('error');
  com.removeClass('error');

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
  }

  if (hour.val().length < 1 && minute.val().length < 1) {
      var regexp = /^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}\s[0-9]{1,2}:[0-9]{1,2}$/;
      if (!regexp.test(start.val())) {
          start.addClass('error');
          return false;
      } else if (!regexp.test(end.val())) {
          end.addClass('error');
          return false;
      }

  } else {
      if (!/^[0-9]*$/.test(hour.val())) {
          hour.addClass('error');
          return false;
      } else if (!/^[0-9]*$/.test(minute.val())) {
          minute.addClass('error');
          return false;
      }
  }

  illegal = decodeURIComponent(illegal);
  var regexp = new RegExp('^[^' + regexp_escape(illegal) + ']{2,64}$');

  if (com.length && !regexp.test(com.val())) {
    com.focus();
    com.addClass('error');
    return false;
  }

  fobject.find('input.data').remove();
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      fobject.append($(element).find('input').clone());
    }
  });

  return true;
}

function valid_comment(fobject, illegal) {
  var fobject = $(fobject);
  var com = fobject.find('#comment');

  com.removeClass('error');
  illegal = decodeURIComponent(illegal);
  var regexp = new RegExp('^[^' + regexp_escape(illegal) + ']{2,64}$');

  if (com.length && !regexp.test(com.val())) {
    com.focus();
    com.addClass('error');
    return false;
  }

  fobject.find('input.data').remove();
  $('table#alert').find('span.checkbox').each(function (index, element) {
    if (testCheckbox(element)) {
      fobject.append($(element).find('input').clone());
    }
  });

  return true;
}

function valid_disable(fobject, illegal) {
  return valid_comment(fobject, illegal);
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

function clicked_generic_action(action, require_items, extra_target) {
  if (typeof(require_items) == 'undefined') {
    require_items = true;
  }

  var fobject = $('\
    <form action="" method="post" style="display: none">\
      <input type="hidden" name="action" value="' + action + '" />\
    </form>\
  ');

  if (require_items) {
    var selected = $('table#alert').find('span.checkbox span.checked');

    if (selected.length) {
      $('table#alert').find('span.checkbox').each(function (index, element) {
        if (testCheckbox(element)) {
          fobject.append($(element).find('input').clone());
        }
      });

    } else {
      blink_checkboxes();
      return;
    }
  }

  if (typeof(extra_target) == 'string') {
    fobject.append('<input type="hidden" name="target[]" value="' + extra_target + '" />');
  }

  $('body').append(fobject);
  fobject.submit();
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
  return false;
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

function checkCheckbox(object, check) {
  if (typeof(check) == 'undefined') {
    check = true;
  }

  if (check) {
    $(object).find('span').addClass('checked');
  } else {
    $(object).find('span').removeClass('checked');
  }
}

function blink_button(jObject) {
    if (!jObject.length)
        return;

    window.setTimeout(
        function () {
            jObject.toggleClass('opacity_10');
            blink_button(jObject);
        },
        280
    );
}

/* This function is used to append words to the search input when clicking
 * on a link of the alert table. It won't be called if the "quick search"
 * option is enabled.
 *
 * We look for the first potential boolean operator in the search input
 * If found, we use it to separate the new word from the others, except if
 * the last character of the search input is already an operator.
 */
function add_filter(key, value)
{
    value = $.trim(value);
    if (value.length == 0)
        return;

    var search_jobject = $('input#filtering');
    if (search_jobject.length != 1)
        return;

    var search_filter = [];
    var existing = $.trim(search_jobject.val());
    var separator;

    if (existing.length) {
        search_filter.push(existing);

        /* don't add operator if the last character is an operator */
        if (((separator = existing.indexOf('&')) > -1 ||
             (separator = existing.indexOf('|')) > -1) &&
            existing.charAt(existing.length - 1) != '&' &&
            existing.charAt(existing.length - 1) != '|' &&
            existing.charAt(existing.length - 1) != '!')
            search_filter.push(existing.charAt(separator));
    }

    if (key.length)
        search_filter.push(key + ':' + value);
    else
        search_filter.push(value);

    search_jobject.val(search_filter.join(' '));
}
