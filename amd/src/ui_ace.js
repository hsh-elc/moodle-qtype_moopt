// Copied from https://github.com/trampgeek/moodle-qtype_coderunner and adjusted to our needs.

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript to interface to the Ace editor, which is used both in
 * the author editing page and by the student question submission page.
 * The class defined in this module is a plugin for the InterfaceWrapper class
 * declared in userinterfacewrapper.js. See that file for an explanation of
 * the interface to this module.
 *
 * A special case behaviour of the AceWrapper is that it needs to know
 * the Programming language that is being edited. This MUST be provided in
 * the constructor templateParams parameter (an associative array) as a string
 * with key 'lang'.
 *
 * @package    qtype
 * @subpackage coderunner
 * @copyright  Richard Lobb, 2015, 2017, The University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Thanks to Ulrich Dangel for the initial implementation of Ace within
// CodeRunner.

// WARNING: The ace editor must have already been loaded before this
// module is used, as it assumes window.ace exists.

define(['jquery'], function ($) {

    function AceWrapper(textareaId, w, h, params) {
        // Constructor for the Ace interface object.

        var textarea = $(document.getElementById(textareaId)),
                focused = textarea[0] === document.activeElement,
                lang = params.lang,
                session;

        try {
            window.ace.require("ace/ext/language_tools");
            this.modelist = window.ace.require('ace/ext/modelist');

            this.textarea = textarea;
            this.enabled = false;
            this.contents_changed = false;
            this.capturingTab = false;
            this.clickInProgress = false;

            this.editNode = $("<div></div>"); // Ace editor manages this.
            this.editNode.css({
                resize: 'none',
                height: h,
                width: "100%"
            });

            this.editor = window.ace.edit(this.editNode.get(0));
            if (textarea.prop('readonly')) {
                this.editor.setReadOnly(true);
            }

            this.editor.setOptions({
                enableBasicAutocompletion: true,
                newLineMode: "unix"
            });
            this.editor.$blockScrolling = Infinity;

            session = this.editor.getSession();
            session.setValue(this.textarea.val());

            this.setLanguage(lang);
            this.editor.setTheme('ace/theme/chrome');

            this.setEventHandlers(textarea);
            this.captureTab();

            // Try to tell Moodle about parts of the editor with z-index.
            // It is hard to be sure if this is complete. ACE adds all its CSS using JavaScript.
            // Here, we just deal with things that are known to cause a problem.
            $('.ace_gutter').addClass('moodle-has-zindex');

            textarea.hide();
            if (focused) {
                this.editor.focus();
                this.editor.navigateFileEnd();
                /*
                 var session = this.editor.getSession(),
                 lines = session.getLength();
                 this.editor.gotoLine(lines, session.getLine(lines - 1).length);
                 */
            }
            this.aceLabel = $('.answerprompt');
            this.aceLabel.attr('for', 'ace_' + textareaId);

            this.aceTextarea = $('.ace_text-input');
            this.aceTextarea.attr('id', 'ace_' + textareaId);
            this.fail = false;
        } catch (err) {
            // Something ugly happened. Probably ace editor hasn't been loaded.
            this.fail = true;
        }
    }

    AceWrapper.prototype.failed = function () {
        return this.fail;
    };

    AceWrapper.prototype.failMessage = function () {
        return 'ace_ui_notready';
    };

    // Sync to TextArea.
    AceWrapper.prototype.sync = function () {
        // Nothing to do ... always sync'd.
    };

    AceWrapper.prototype.setLanguage = function (language) {
        var session = this.editor.getSession(),
                mode = this.findMode(language);
        if (mode) {
            session.setMode(mode.mode);
        }
    };

    AceWrapper.prototype.getElement = function () {
        return this.editNode;
    };

    AceWrapper.prototype.captureTab = function () {
        this.capturingTab = true;
        this.editor.commands.bindKeys({'Tab': 'indent', 'Shift-Tab': 'outdent'});
    };

    AceWrapper.prototype.releaseTab = function () {
        this.capturingTab = false;
        this.editor.commands.bindKeys({'Tab': null, 'Shift-Tab': null});
    };

    AceWrapper.prototype.setEventHandlers = function () {
        var TAB = 9,
                ESC = 27,
                KEY_M = 77,
                t = this;

        this.editor.getSession().on('change', function () {
            t.textarea.val(t.editor.getSession().getValue());
            t.contents_changed = true;
        });

        this.editor.on('blur', function () {
            if (t.contents_changed) {
                t.textarea.trigger('change');
            }
        });

        this.editor.on('mousedown', function () {
            // Event order seems to be (\ is where the mouse button is pressed, / released):
            // Chrome: \ mousedown, mouseup, focusin / click.
            // Firefox/IE: \ mousedown, focusin / mouseup, click.
            t.clickInProgress = true;
        });

        this.editor.on('focus', function () {
            if (t.clickInProgress) {
                t.captureTab();
            } else {
                t.releaseTab();
            }
        });

        this.editor.on('click', function () {
            t.clickInProgress = false;
        });

        this.editor.container.addEventListener('keydown', function (e) {
            if (e.which === undefined || e.which !== 0) { // Normal keypress?
                if (e.keyCode === KEY_M && e.ctrlKey && !e.altKey) {
                    if (t.capturingTab) {
                        t.releaseTab();
                    } else {
                        t.captureTab();
                    }
                    e.preventDefault(); // Firefox uses this for mute audio in current browser tab.
                } else if (e.keyCode === ESC) {
                    t.releaseTab();
                } else if (!(e.shiftKey || e.ctrlKey || e.altKey || e.keyCode == TAB)) {
                    t.captureTab();
                }
            }
        }, true);
    };

    AceWrapper.prototype.destroy = function () {
        var focused;
        if (!this.fail) {
            // Proceed only if this wrapper was correctly constructed.
            focused = this.editor.isFocused();
            this.textarea.val(this.editor.getSession().getValue()); // Copy data back.
            this.editor.destroy();
            $(this.editNode).remove();
            if (focused) {
                this.textarea.focus();
                this.textarea[0].selectionStart = this.textarea[0].value.length;
            }
        }
    };

    AceWrapper.prototype.hasFocus = function () {
        return this.editor.isFocused();
    };

    AceWrapper.prototype.findMode = function (language) {
        var candidate,
            filename,
            result,
            candidates = [], // List of candidate modes.
            nameMap = {
                'octave': 'matlab',
                'nodejs': 'javascript',
                'c#': 'cs'};

        if (typeof language !== 'string') {
            return undefined;
        }
        if (language.toLowerCase() in nameMap) {
            language = nameMap[language.toLowerCase()];
        }

        candidates = [language, language.replace(/\d+$/, "")];
        for (var i = 0; i < candidates.length; i++) {
            candidate = candidates[i];
            filename = "input." + candidate;
            result = this.modelist.modesByName[candidate] ||
                    this.modelist.modesByName[candidate.toLowerCase()] ||
                    this.modelist.getModeForPath(filename) ||
                    this.modelist.getModeForPath(filename.toLowerCase());

            if (result && result.name !== 'text') {
                return result;
            }
        }
        return undefined;
    };

    AceWrapper.prototype.resize = function (w, h, force) {
        this.editNode.height(h);
        this.editNode.width(w);
        this.editor.resize(force);
    };

    AceWrapper.prototype.getLineHeight = function () {
        return this.editor.renderer.layerConfig.lineHeight;
    };

    AceWrapper.prototype.getHScrollHeight = function () {
        if (this.editor.renderer.$horizScroll) {
            return this.editor.renderer.scrollBarH.getHeight();
        }
        return 0;
    };

    return {
        Constructor: AceWrapper
    };
});
