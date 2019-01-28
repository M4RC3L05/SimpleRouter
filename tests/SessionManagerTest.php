<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\SessionManager;


class SessionManagerTest extends TestCase
{
    public function test_it_should_create_a_session_manager()
    {
        try {
            $sm = new SessionManager();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function test_it_should_set_to_session()
    {

        $sm = new SessionManager();
        $sm->set("a", "b");
        $this->assertEquals(["sr_session@@a" => "b"], $_SESSION);

        $sm->set("a", "b", false);
        $this->assertEquals(["sr_session@@a" => "b", "a" => "b"], $_SESSION);
    }

    public function test_it_should_get_by_key_from_session()
    {
        $sm = new SessionManager();
        $sm->set("a", "b");

        $fromsess = $sm->get("a");
        $this->assertEquals("b", $fromsess);

        $sm->set("c", "b", false);

        $fromsess2 = $sm->get("c", false);
        $this->assertEquals("b", $fromsess2);

    }

    public function test_it_should_remove_by_key_from_session()
    {
        $sm = new SessionManager();
        $sm->set("a", "b");

        $sm->remove("a");
        $this->assertEquals([], $_SESSION);

        $sm->set("v", "b", false);

        $sm->remove("v", false);
        $this->assertEquals([], $_SESSION);
    }

    public function test_it_should_destroy_the_session()
    {
        $sm = new SessionManager();
        $sm->set("a", "b");
        $sm->set("v", "b", false);

        $this->assertEquals(["sr_session@@a" => "b", "v" => "b"], $_SESSION);

        $sm->destroyAll();

        $this->assertEquals([], $_SESSION);

    }

    public function test_it_should_destroy_all_by_prefix()
    {
        $sm = new SessionManager();
        $sm->set("a", "b");
        $sm->set("v", "b", false);

        $this->assertEquals(["sr_session@@a" => "b", "v" => "b"], $_SESSION);

        $sm->destroyByPrefix();
        $this->assertEquals(["v" => "b"], $_SESSION);
    }
}
