<?php
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
 * Alias manager.
 *
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class alias_manager {


    /** Insert the data into database.
     * @param string $friendlyurl
     * @param string $destinationurl
     * @return bool true if successful
     */
    public function create_alias(string $friendlyurl, string $destinationurl): bool {
        global $DB;
        $recordtoinsert = new stdClass();
        $recordtoinsert->friendly = $friendlyurl;
        $recordtoinsert->destination = $destinationurl;
        try {
            return $DB->insert_record('alias', $recordtoinsert, false);
        } catch (dml_exception $e) {
            return false;
        }
    }

    /** Get alias with pagination and filter
     * @param int $currentpage
     * @param string $query
     * @return array of alias with pagination information, empty array if not found
     * @throws dml_exception
     */
    public function get_aliases(int $currentpage, string $query): array {
        global $DB;
        $pagesize = 3;
        $page = $currentpage ?? 0;
        $select = strlen($query) != 0 ?
            $DB->sql_like('friendly', ':friendly')
            : '';
        $params = ['friendly' => '%'.$DB->sql_like_escape($query).'%',
                ];
        $count = $DB->count_records_select('alias', $select, $params);
        try {
            $aliases = $DB->get_records_select(
                'alias',
                $select,
                $params,
                'id',
                '*',
                $pagesize * $page,
                $pagesize);
            return [
                'aliases' => array_values($aliases),
                'page' => $page,
                'pages' => ceil($count / $pagesize),
                'count' => $count,
            ];
        } catch (dml_exception $e) {
            return [];
        }
    }

    /** Get a single record from it's id.
     * @param int $aliasid the record we're trying to get
     * @return object|false record data or false if not found.
     */
    public function get_alias_by_id(int $aliasid) {
        global $DB;
            return $DB->get_record('alias', ['id' => $aliasid]);
    }

    /** Update details for a single alias.
     * @param int $aliasid the alias we're trying to update.
     * @param string $friendlyurl the new friendly url.
     * @param string $destinationurl the new destination url.
     * @return bool the alias data or false if not found.
     */
    public function update_alias(int $aliasid, string $friendlyurl, string $destinationurl) {
        global $DB;
        $object = new stdClass();
        $object->id = $aliasid;
        $object->friendly = $friendlyurl;
        $object->destination = $destinationurl;

        try {
            return $DB->update_record('alias', $object);
        } catch (dml_exception $e) {
            return false;
        }

    }

    /** Delete an alias.
     * @param  int $aliasid the alias we're trying to delete.
     * @return bool true if success
     */
    public function delete_alias(int $aliasid) {
        global $DB;
        return $DB->delete_records('alias', ['id' => $aliasid]);
    }
}
