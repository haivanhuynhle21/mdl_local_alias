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
 * Unit tests for alias_manager class
 * @package   local_alias
 * @copyright 2022, Van Huynh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/alias/lib.php');
require_once($CFG->dirroot . '/local/alias/classes/alias_manager.php');

/**
 * Unit tests for alias_manager class
 * @package   local_alias
 * @copyright 2022, Van Huynh
 */
class local_alias_manager_test extends advanced_testcase {
    /**
     * Test that we can create an alias.
     */
    public function test_create_alias() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();
        $aliases = $manager->get_aliases(0, '');
        $this->assertEmpty($aliases['aliases']);

        $result = $manager->create_alias('http://localhost/frontendmasters', 'http://localhost/course.php?id=99');
        $this->assertTrue($result);
        $aliases = $manager->get_aliases(0, '');
        $this->assertNotEmpty($aliases);

        $this->assertCount(1, $aliases['aliases']);
        $alias = array_pop($aliases['aliases']);

        $this->assertEquals('http://localhost/frontendmasters', $alias->friendly);
        $this->assertEquals('http://localhost/course.php?id=99', $alias->destination);
    }

    /**
     * Test that we can update an alias.
     */
    public function test_update_alias() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        $manager->create_alias('http://localhost/frontendmasters', 'http://localhost/course.php?id=99');
        $aliases = $manager->get_aliases(0, '');
        $alias = array_pop($aliases['aliases']);

        $manager->update_alias($alias->id, 'http://localhost/editedalias', 'http://localhost/course.php?id=999');

        $updatedalias = $manager->get_alias_by_id($alias->id);

        $this->assertEquals('http://localhost/editedalias', $updatedalias->friendly);
        $this->assertEquals('http://localhost/course.php?id=999', $updatedalias->destination);
    }

    /**
     * Test that we can delete an alias.
     */
    public function test_delete_alias() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        $manager->create_alias('http://localhost/frontendmasters', 'http://localhost/course.php?id=99');
        $aliases = $manager->get_aliases(0, '');

        $this->assertCount(1, $aliases['aliases']);
        $alias = array_pop($aliases['aliases']);

        $result = $manager->delete_alias($alias->id);
        $this->assertTrue($result);

        $this->assertFalse($manager->get_alias_by_id($alias->id));
        $this->assertEmpty( $aliases['aliases']);
    }

    /**
     * Test that we can get details of an alias by id.
     */
    public function test_get_alias_by_id() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        $manager->create_alias('http://localhost/frontendmasters', 'http://localhost/course.php?id=99');
        $aliases = $manager->get_aliases(0, '');

        $alias = array_pop($aliases['aliases']);

        $result = $manager->get_alias_by_id($alias->id);

        $this->assertEquals('http://localhost/frontendmasters', $result->friendly);
        $this->assertEquals('http://localhost/course.php?id=99', $result->destination);
    }

    /**
     * Test that we can search for alias(es) by keyword.
     */
    public function test_get_aliases_by_friendly_url() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        $manager->create_alias('http://localhost/frontendmasters', 'http://localhost/course.php?id=99');
        $aliases = $manager->get_aliases(0, 'frontendmasters');

        $this->assertNotEmpty($aliases);

        $this->assertCount(1, $aliases['aliases']);
        $alias = array_pop($aliases['aliases']);

        $this->assertEquals('http://localhost/frontendmasters', $alias->friendly);
        $this->assertEquals('http://localhost/course.php?id=99', $alias->destination);
    }

    /**
     * Test that we can get aliases with pagination (server side).
     */
    public function test_get_aliases_with_pagination() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        for ($i = 1; $i <= 7; $i++) {
            $manager->create_alias("http://localhost/{$i}", "http://localhost/course.php?id={$i}");
        }

        $aliasespage0 = $manager->get_aliases(0, '');
        $this->assertNotEmpty($aliasespage0);
        $this->assertCount(3, $aliasespage0['aliases']);
        $this->assertEquals(0, $aliasespage0['page']);
        $this->assertEquals(3, $aliasespage0['pages']);
        $this->assertEquals(7, $aliasespage0['count']);

        $aliasespage1 = $manager->get_aliases(1, '');
        $this->assertNotEmpty($aliasespage1);
        $this->assertCount(3, $aliasespage1['aliases']);
        $this->assertEquals(1, $aliasespage1['page']);
        $this->assertEquals(3, $aliasespage1['pages']);
        $this->assertEquals(7, $aliasespage1['count']);

        $aliasespage2 = $manager->get_aliases(2, '');
        $this->assertNotEmpty($aliasespage2);
        $this->assertCount(1, $aliasespage2['aliases']);
        $this->assertEquals(2, $aliasespage2['page']);
        $this->assertEquals(3, $aliasespage2['pages']);
        $this->assertEquals(7, $aliasespage2['count']);

        $this->assertEquals('http://localhost/1', $aliasespage0['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=1', $aliasespage0['aliases'][0]->destination);

        $this->assertEquals('http://localhost/4', $aliasespage1['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=4', $aliasespage1['aliases'][0]->destination);

        $this->assertEquals('http://localhost/7', $aliasespage2['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=7', $aliasespage2['aliases'][0]->destination);

    }

    /**
     * Test that we can search for alias(es) with pagination.
     */
    public function test_get_aliases_with_search_and_pagination() {
        $this->resetAfterTest();
        $this->setUser(2);
        $manager = new alias_manager();

        for ($i = 1; $i <= 7; $i++) {
            $manager->create_alias("http://localhost/esaka{$i}", "http://localhost/course.php?id={$i}");
        }

        $aliasespage0 = $manager->get_aliases(0, 'esaka');
        $this->assertNotEmpty($aliasespage0);
        $this->assertCount(3, $aliasespage0['aliases']);
        $this->assertEquals(0, $aliasespage0['page']);
        $this->assertEquals(3, $aliasespage0['pages']);
        $this->assertEquals(7, $aliasespage0['count']);

        $aliasespage1 = $manager->get_aliases(1, 'esaka');
        $this->assertNotEmpty($aliasespage1);
        $this->assertCount(3, $aliasespage1['aliases']);
        $this->assertEquals(1, $aliasespage1['page']);
        $this->assertEquals(3, $aliasespage1['pages']);
        $this->assertEquals(7, $aliasespage1['count']);

        $aliasespage2 = $manager->get_aliases(2, 'esaka');
        $this->assertNotEmpty($aliasespage2);
        $this->assertCount(1, $aliasespage2['aliases']);
        $this->assertEquals(2, $aliasespage2['page']);
        $this->assertEquals(3, $aliasespage2['pages']);
        $this->assertEquals(7, $aliasespage2['count']);

        $this->assertEquals('http://localhost/esaka1', $aliasespage0['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=1', $aliasespage0['aliases'][0]->destination);

        $this->assertEquals('http://localhost/esaka4', $aliasespage1['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=4', $aliasespage1['aliases'][0]->destination);

        $this->assertEquals('http://localhost/esaka7', $aliasespage2['aliases'][0]->friendly);
        $this->assertEquals('http://localhost/course.php?id=7', $aliasespage2['aliases'][0]->destination);
    }
}
