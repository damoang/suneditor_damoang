'use strict';

import 'suneditor/src/assets/css/suneditor.css';
import 'suneditor/src/assets/css/suneditor-contents.css';

import plugins from 'suneditor/src/plugins';
import suneditor from 'suneditor/src/suneditor';

if (!window.SUNEDITOR) {
    Object.defineProperty(window, 'SUNEDITOR', {
        enumerable: true,
        writable: false,
        configurable: false,
        value: suneditor.init({
            plugins: plugins
        })
    });
}