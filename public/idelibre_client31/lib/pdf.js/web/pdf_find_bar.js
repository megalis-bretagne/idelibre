/* -*- Mode: Java; tab-width: 2; indent-tabs-mode: nil; c-basic-offset: 2 -*- */
/* Copyright 2012 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/* globals FindStates, mozL10n */

'use strict';

/**
 * Creates a "search bar" given a set of DOM elements that act as controls
 * for searching or for setting search preferences in the UI. This object
 * also sets up the appropriate events for the controls. Actual searching
 * is done by PDFFindController.
 */
var PDFFindBar = (function PDFFindBarClosure() {
    function PDFFindBar(options) {
        console.log("options");
        console.log(options);
        this.opened = false;
        this.bar = options.bar || null;
        this.toggleButton = options.toggleButton || null;
        this.findField = options.findField || null;
        this.highlightAll = options.highlightAllCheckbox || null;
        this.caseSensitive = options.caseSensitiveCheckbox || null;
        this.findMsg = options.findMsg || null;
        this.findStatusIcon = options.findStatusIcon || null;
        this.findPreviousButton = options.findPreviousButton || null;
        this.findNextButton = options.findNextButton || null;
        this.findController = options.findController || null;

        if (this.findController === null) {
            throw new Error('PDFFindBar cannot be used without a ' +
                    'PDFFindController instance.');
        }

        // Add event listeners to the DOM elements.
        var self = this;
        /*  this.toggleButton.addEventListener('click', function() {
         self.toggle();
         });
         */


        var nothing = function () {
            self.dispatchEvent('');
        };

//    this.findField.addEventListener('input', function() {
//      self.dispatchEvent('');
//    });
        this.findField.addEventListener('input', nothing);


//  /*  this.bar.addEventListener('keydown', function(evt) {
//      switch (evt.keyCode) {
//        case 13: // Enter
//          if (evt.target === self.findField) {
//            self.dispatchEvent('again', evt.shiftKey);
//          }
//          break;
//        case 27: // Escape
//          self.close();
//          break;
//      }
//    });
//*/
//


        var againTrue = function () {
            console.log("againTrue()")
            self.dispatchEvent('again', true);
        };
//
//    this.findPreviousButton.addEventListener('click', function() {
//      self.dispatchEvent('again', true);
//    });
        this.findPreviousButton.addEventListener('click', againTrue);
        console.log("previous");
        console.log(this.findPreviousButton);

        var againFalse = function () {
            console.log("againFalse()")
            self.dispatchEvent('again', false);
        };

//    this.findNextButton.addEventListener('click', function() {
//      self.dispatchEvent('again', false);
//    });
//
        this.findNextButton.addEventListener('click', againFalse);

        var highlightallchange = function () {
            self.dispatchEvent('highlightallchange');
        };
//    this.highlightAll.addEventListener('click', function() {
//      self.dispatchEvent('highlightallchange');
//    });

        this.highlightAll.addEventListener('click', highlightallchange);

        var casesensitivitychange = function () {
            self.dispatchEvent('casesensitivitychange');
        };

//    this.caseSensitive.addEventListener('click', function() {
//      self.dispatchEvent('casesensitivitychange');
//    });
        this.caseSensitive.addEventListener('click', casesensitivitychange);


        this.deleteListeners = function () {
            console.log('REMOVE');
            this.findField.removeEventListener('input', nothing);
            this.caseSensitive.removeEventListener('click', casesensitivitychange);
            this.highlightAll.removeEventListener('click', highlightallchange);
            this.findNextButton.removeEventListener('click', againFalse);
            this.findPreviousButton.removeEventListener('click', againTrue);

//        window.removeEventListener('input',  this.findField);
//        window.removeEventListener('click', this.findPreviousButton);
//        window.removeEventListener('click', this.findNextButton);
//        window.removeEventListener('click', this.highlightAll);
//        window.removeEventListener('click', this.caseSensitive);
//        
//        this.findField.removeEventListener('input');
//        this.findPreviousButton.removeEventListener('click');
//        this.findNextButton.removeEventListener('click');
//        this.highlightAll.removeEventListener('click');
//        this.caseSensitive.removeEventListener('click');
        };

    }

    PDFFindBar.prototype = {
        dispatchEvent: function PDFFindBar_dispatchEvent(type, findPrev) {
            var event = document.createEvent('CustomEvent');
            event.initCustomEvent('find' + type, true, true, {
                query: this.findField.value,
                caseSensitive: this.caseSensitive.checked,
                highlightAll: this.highlightAll.checked,
                findPrevious: findPrev
            });
            return window.dispatchEvent(event);
        },
        updateUIState: function PDFFindBar_updateUIState(state, previous) {
            var notFound = false;
            var findMsg = '';
            var status = '';

            switch (state) {
                case FindStates.FIND_FOUND:
                    break;

                case FindStates.FIND_PENDING:
                    status = 'pending';
                    break;

                case FindStates.FIND_NOTFOUND:
                    //Rémi : TODO afficher un message 0 ocurence ou ecrire le texte en rouge !
                    //findMsg = mozL10n.get('find_not_found', null, 'Phrase not found');
                    notFound = true;
                    break;

                    /*case FindStates.FIND_WRAPPED:
                     if (previous) {
                     findMsg = mozL10n.get('find_reached_top', null,
                     'Reached top of document, continued from bottom');
                     } else {
                     findMsg = mozL10n.get('find_reached_bottom', null,
                     'Reached end of document, continued from top');
                     }
                     break;*/
            }

            if (notFound) {

                this.findField.classList.add('notFound');
            } else {
                this.findField.classList.remove('notFound');
            }

            this.findField.setAttribute('data-status', status);
            this.findMsg.textContent = findMsg;
        },
        open: function PDFFindBar_open() {
            if (!this.opened) {
                this.opened = true;
                this.toggleButton.classList.add('toggled');
                this.bar.classList.remove('hidden');
            }
            this.findField.select();
            this.findField.focus();
        },
        close: function PDFFindBar_close() {
            if (!this.opened) {
                return;
            }
            this.opened = false;
            this.toggleButton.classList.remove('toggled');
            this.bar.classList.add('hidden');
            this.findController.active = false;
        },
        toggle: function PDFFindBar_toggle() {
            if (this.opened) {
                this.close();
            } else {
                this.open();
            }
        }
    };
    return PDFFindBar;
})();
