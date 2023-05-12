<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_usuario = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expiracion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUsuario(): ?User
    {
        return $this->id_usuario;
    }

    public function setIdUsuario(?User $id_usuario): self
    {
        $this->id_usuario = $id_usuario;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiracion(): ?\DateTimeInterface
    {
        return $this->expiracion;
    }

    public function setExpiracion(\DateTimeInterface $expiracion): self
    {
        $this->expiracion = $expiracion;

        return $this;
    }

    public function toArray(): array
    {
        $datosPersonalesArray = [
            'id_usuario' => $this->id_usuario,
            'token' => $this->token,
            'expiracion' => $this->expiracion
        ];
        return $datosPersonalesArray;
    }

    public function fromJson($content): void
    {
        $content = json_decode($content, true);

        $this->id_usuario = $content['id_usuario'];
        $this->token = $content['token'];
        $this->expiracion = DateTime::createFromFormat('Y-m-d H:i:s', $content['expiracion']);
    }
}
