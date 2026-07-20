<?php

namespace Tests\Unit;

use Acelle\Model\SendingServerTwilio;
use Acelle\Model\SendingServer;
use Tests\TestCase;

class SendingServerTwilioTest extends TestCase
{
    /**
     * Test that the SendingServerTwilio class is compatible with its parent.
     *
     * @return void
     */
    public function testSendingServerTwilioCompatibility()
    {
        // Check if the class exists
        $this->assertTrue(class_exists(SendingServerTwilio::class));
        
        // Check if it extends the right parent
        $reflection = new \ReflectionClass(SendingServerTwilio::class);
        $this->assertEquals(SendingServer::class, $reflection->getParentClass()->getName());
        
        // Try to instantiate the class
        $server = new SendingServerTwilio();
        $this->assertInstanceOf(SendingServerTwilio::class, $server);
        
        // Set the type to Twilio and verify we can call the parent methods without errors
        $server->type = SendingServer::TYPE_TWILIO;
        
        // This should use the parent implementation and not throw any exceptions
        $rules = $server->getRules(); 
        $this->assertIsArray($rules);
        
        // The static method should also work (would have caused a fatal error if incompatible)
        $staticRules = SendingServer::rules(SendingServer::TYPE_TWILIO);
        $this->assertIsArray($staticRules);

        // If we get here without errors, the test passes
        $this->assertTrue(true, "SendingServerTwilio class is compatible with its parent");
    }
}
