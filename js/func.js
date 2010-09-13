/*
  OpenPom $Revision$
  $HeadURL$
 
  Copyright 2010, Exosec
  Licensed under GPL Version 2.
  http://www.gnu.org/licenses/
 
  $Date$
*/

function selectall(num) {
  for (i=1; i<=num; i++)
    selectline(i);
}

function selectline(i) {
  elem = document.getElementById("check_" + i);
  if (elem == null) return;
  if (elem.checked == true)
    elem.checked = false;
  else
    elem.checked = true;
}

function getallselectline(num,form) {
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
    }
  }
}

function autorefresh() { 
  /* set next timeout */
  setTimeout(autorefresh, 1000, null);
  /* check if any line is checked */
  for (i=1; i<=100; i++) {
    if (document.getElementById("check_" + i) == null)
      break;
    if (document.getElementById("check_" + i).checked == true) {
      refresh.innerHTML = '<img src="img/stop.png" border="0" />';
      return;
    }
  }
  mytime--;
  var my_get = location.href.replace(/^.*[\/\\]/g, ''); 
  if (mytime == 0)
    window.location.href = my_get;
  refresh.innerHTML = mytime;
}

function XMLHttpRequestSurcouche(zeasy) { 
  /* objet XMLHttpRequest */ 
  this.rq;
  var easy = zeasy; 
  var myrq;
  this.XMLHttpRequestResponse = function() { 
    if (myrq.readyState == 4 && myrq.status == 200) {
      /* display */ 
      popup.innerHTML = myrq.responseText; 
      /* hide */
      cache[easy] = popup.innerHTML;
    }
  };
  myrq = new XMLHttpRequest(); 
  myrq.onreadystatechange = this.XMLHttpRequestResponse;
  this.rq = myrq;
}

function get_data(type, id) {
  if (popup.style.left != "" && popup.style.top != "")
    popup.style.visibility = "visible";
  else
    popup.style.visibility = "hidden";

  current_data_displayed = type + ":" + id;
  if (cache[current_data_displayed]) {
    popup.innerHTML = cache[current_data_displayed];
    return;
  }

  popup.innerHTML = "<img src=\"img/ajax_loading.gif\" />";
  cache[current_data_displayed] = "<img src=\"img/ajax_loading.gif\" />";

  var xhr = new XMLHttpRequestSurcouche(current_data_displayed);
  xhr.rq.open("GET", "status.php?type="+type+"&id="+id,  true);
  xhr.rq.send(null); 
}

function WhereMouse(e) {
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
  popup.style.left = x + "px";
  //popup.style.left = window.innerWidth - 600 + "px";
  popup.style.top = document.body.scrollTop + "px";
}

function hide_data() {                                                                     
  popup.style.visibility = "hidden";
}

function add_input(name, form) {
  var my_form = document.getElementById(form);
  var el = document.createElement('input');
  el.type = 'hidden';
  el.name = name;
  el.value = 1;
  my_form.appendChild(el);
}
