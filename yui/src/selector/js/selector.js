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
 * Javascript library
 *
 * @package    local_bulkmeta
 * @copyright  2014 Troy Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.local_bulkmeta = M.local_bulkmeta || {};
M.local_bulkmeta.selector = {
    /**
     * The script which handles the query. URL params are
     * substituted.
     *
     * @property AJAX_SEARCH_URL
     * @type String
     */
    AJAX_SEARCH_URL: M.cfg.wwwroot + '/local/bulkmeta/search.ajax.php?searchtext={query}&id={id}&sesskey={sesskey}',

    /**
     * The course identifer of course in relation to meta linked courses.
     *
     * @property courseid
     * @type Int
     * @default null
     */
    courseid : null,
    /**
     * Name that will be used to reference form controls.
     *
     * @property name
     * @type String
     * @default null
     */
    name : null,
    /**
     * References to names various search options. Checkboxes.
     *
     * @property options
     */
    options : ['searchanywhere', 'fullname', 'idnumber', 'summary'],

    init: function(courseid, name) {

        this.courseid = courseid;
        this.name = name;

        Y.one('#id_' + name + '_searchbutton').remove();
        Y.one('#id_' + name + '_clearbutton').remove();

        this.searchfield = Y.one('#id_' + name + '_searchtext');
        this.searchfield.plug(Y.Plugin.AutoComplete);
        this.searchfield.ac.set('source', this.prepare_url(this.AJAX_SEARCH_URL, courseid));
        this.searchfield.ac.set('resultListLocator', 'results');
        this.searchfield.ac.set('resultTextLocator', 'fullname');
        this.searchfield.ac.on('results', Y.bind(this.fill_listbox, this));
        this.searchfield.on('key', Y.bind(this.backspace, this), 'backspace');

        this.listbox = Y.one('#id_' + name + '_link');

        for (var i in this.options) {
            var optionname = this.options[i];
            var optionnode = Y.one('#id_' + name + '_option_' + optionname);
            if (optionnode) {
                optionnode.on('click', this.set_user_preference, null, name, optionname);
            }
        }

    },

    backspace : function() {
        if (this.get_search_text() === '') {
            this.searchfield.ac.sendRequest('');
        }
        this.searchfield.ac.focus();
    },

    /**
     * Gets the search text
     * @return String the value to search for, with leading and trailing whitespace trimmed.
     */
    get_search_text : function() {
        return this.searchfield.get('value').toString().replace(/^ +| +$/, '');
    },

    prepare_url : function (url, courseid) {
        return Y.Lang.sub(url, {
            sesskey: M.cfg.sesskey,
            id: courseid
        });
    },

    fill_listbox : function(e) {
        var data = e.data;
        var courses = {};
        var optgroup = Y.Node.create('<optgroup></optgroup>');
        this.listbox.all('optgroup').each(function(optgroup){
            optgroup.all('option').each(function(option){
                if (option.get('selected')) {
                    courses[option.get('value')] = {
                        id : option.get('value'),
                        name : option.get('innerText') || option.get('textContent'),
                        disabled: option.get('disabled')
                    };
                }
                option.remove();
            }, this);
            optgroup.remove();
        }, this);

        count = 0;
        for (var id in data.result.results) {
            //console.log(id + ' ' + data.result.results[id]);
            var option = Y.Node.create('<option value="' + id + '">' + data.result.results[id] + '</option>');
            optgroup.append(option);
            count++;
        }
        optgroup.set('label', data.result.label);
        this.listbox.append(optgroup);
    },

    /**
     * Sets a user preference for the options tracker
     * @param {Y.Event|null} e
     * @param {string} name The general name used when defining the control
     * @param {string} option The name of the preference to set
     */
    set_user_preference : function(e, name, option) {
       M.util.set_user_preference(name + '_option_' + option,
                                  Y.one('#id_' + name + '_option_' + option).get('checked'));
    }
};
