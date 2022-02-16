<?php

declare(strict_types=1);

namespace potibm\SceneIdOauth2;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SceneIdUser implements ResourceOwnerInterface
{
    private int $id;

    private string $email;

    private string $firstname;

    private string $lastname;

    private string $displayname;

    public function __construct(array $user)
    {
        $this->id = (int) $user['id'];
        $this->email = $user['email'];
        $this->firstname = $user['first_name'];
        $this->lastname = $user['last_name'];
        $this->displayname = $user['display_name'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getDisplayname(): string
    {
        return $this->displayname;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'displayname' => $this->displayname,
        ];
    }
}
