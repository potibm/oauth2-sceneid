<?php

declare(strict_types=1);

namespace potibm\SceneIdOauth2\Test;

use PHPUnit\Framework\TestCase;
use potibm\SceneIdOauth2\SceneIdUser;

class SceneIdUserTest extends TestCase {
    public function testSettingFromConstructor() {
        $id = rand(0, 1000);
        $email = uniqid();
        $firstName = uniqid();
        $lastName = uniqid();
        $displayName = uniqid();

        $data = [
            'id' => (string) $id,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName
        ];

        $user = new SceneIdUser($data);

        $this->assertEquals($user->getId(), $id);
        $this->assertEquals($user->getEmail(), $email);
        $this->assertEquals($user->getFirstname(), $firstName);
        $this->assertEquals($user->getLastname(), $lastName);
        $this->assertEquals($user->getDisplayname(), $displayName);
        $this->assertEquals([
            'id' => $id,
            'email' => $email,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'displayname' => $displayName
        ], $user->toArray());
    }
}
