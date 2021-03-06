<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2012, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Abhinav Singh nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

require dirname(__FILE__) . '/_bootstrap.php';

if ($argc < 3) {
    echo "Usage: $argv[0] jid pass".PHP_EOL;
    exit;
}

//
// initialize JAXL object with initial config
//
$client = new JAXL(array(
    'jid' => $argv[1],
    'pass' => $argv[2],
    'log_level' => JAXLLogger::INFO
));

$client->require_xep(array(
    '0060'  // Publish-Subscribe
));

//
// add necessary event callbacks here
//

function on_auth_success_callback()
{
    global $client;
    JAXLLogger::info("got on_auth_success cb, jid ".$client->full_jid->to_string());

    // create node
    //$client->xeps['0060']->create_node('pubsub.localhost', 'dummy_node');

    // subscribe
    $client->xeps['0060']->subscribe('pubsub.localhost', 'dummy_node');
}
$client->add_cb('on_auth_success', 'on_auth_success_callback');

function on_auth_failure_callback($reason)
{
    global $client;
    $client->send_end_stream();
    JAXLLogger::info("got on_auth_failure cb with reason $reason");
}
$client->add_cb('on_auth_failure', 'on_auth_failure_callback');

function on_headline_message_callback($stanza)
{
    global $client;
    if (($event = $stanza->exists('event', XEP0060::NS_PUBSUB.'#event'))) {
        JAXLLogger::info("got pubsub event");
    } else {
        JAXLLogger::warning("unknown headline message rcvd");
    }
}
$client->add_cb('on_headline_message', 'on_headline_message_callback');

function on_disconnect_callback()
{
    JAXLLogger::info("got on_disconnect cb");
}
$client->add_cb('on_disconnect', 'on_disconnect_callback');

//
// finally start configured xmpp stream
//
$client->start();
echo "done".PHP_EOL;
