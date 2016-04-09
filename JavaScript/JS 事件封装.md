将事件相关的操作封装到一个变量中。

```JavaScript
var EventUtil = {
  addHandler: function(element,type,handler) {
    if(element.addEventListener) {
      element.addEventListener(type,handler,false);
    }else if(element.attachEvent) {
      element.attachEvent("on"+type,handler);
    }else {
      element["on" +type] = handler;
    }
  },
  removeHandler: function(element,type,handler){
    if(element.removeEventListener) {
      element.removeEventListener(type,handler,false);
    }else if(element.detachEvent) {
      element.detachEvent("on"+type,handler);
    }else {
      element["on" +type] = null;
    }
  },
  getEvent: function(event) {
    return event ? event : window.event;
  },
  getTarget: function(event) {
    return event.target || event.srcElement;
  },
  preventDefault: function(event){
    if(event.preventDefault) {
      event.preventDefault();
    }else {
      event.returnValue = false;
    }
  },
  stopPropagation: function(event) {
    if(event.stopPropagation) {
      event.stopPropagation();
    }else {
      event.cancelBubble = true;
    }
  },
  getRelatedTarget: function(event){
    if (event.relatedTarget){
      return event.relatedTarget;
    } else if (event.toElement){
      return event.toElement;
    } else if (event.fromElement){
      return event.fromElement;
    } else {
      return null;
    }
  },
  getWheelDelta: function(event) {
    if(event.wheelDelta) {
      return event.wheelDelta;
    }else {
      return -event.detail * 40
    }
  },
  getCharCode: function(event) {
    if(typeof event.charCode == 'number') {
      return event.charCode;
    }else {
      return event.keyCode;
    }
  }
};
```



