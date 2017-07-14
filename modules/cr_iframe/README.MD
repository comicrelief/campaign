# CR iFrame module

This module renders an external page in an iframe block.

## Dynamic Sizing

The external webpage can size the iframe dynamically by sending a message in the following format using javascrpt.

```javascript
var itemHeight = 100;
window.parent.postMessage('{"iframe_height":"' + itemHeight + '"}','*');
```

A listener is attatched to the window to detect these changes, it will size the window accordingly when a change is detected.