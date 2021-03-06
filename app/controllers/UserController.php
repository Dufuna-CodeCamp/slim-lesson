<?php

namespace App\Controller;

use DB\DB;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    private $db;

    public function __construct()
    {
        $this->db = (new DB())->connect();
    }

    public function index(Request $request, Response $response, $args)
    {
        try {
            $query = $this->db->prepare("SELECT * FROM users");
            $query->execute();
            $users = $query->fetchAll();
            $response->getBody()->write(json_encode($users));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $ex) {
            $response->getBody()->write(json_encode(["error" => $ex->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getUser(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        try {
            $query = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $query->bindParam(":id", $id);
            $query->execute();
            $user = $query->fetch();

            if (!$user) {
                $response->getBody()->write(json_encode(["error" => "User not found"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $response->getBody()->write(json_encode($user));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $ex) {
            $response->getBody()->write(json_encode(["error" => $ex->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function create(Request $request, Response $response, $args)
    {
        $requestData = $request->getParsedBody();
        $fullName = $requestData['name'];
        $email = $requestData['email'];
        $gender = $requestData['gender'];
        $createdAt = date('Y-m-d H:i:s');

        $sql = "INSERT INTO users (name, email, gender, created_at) VALUES (:name, :email, :gender, :created_at)";

        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':name', $fullName);
            $query->bindParam(':email', $email);
            $query->bindParam(':gender', $gender);
            $query->bindParam(':created_at', $createdAt);
            $query->execute();

            $response->getBody()->write(json_encode(['message' => 'User created successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $ex) {
            $response->getBody()->write(json_encode(["error" => $ex->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $fullName = $request->getParsedBody()['name'];
        $email = $request->getParsedBody()['email'];

        try{
            $query = $this->db->prepare("UPDATE users SET name = :name, email = :email WHERE id = $id");
            $query->bindParam(':name', $fullName);
            $query->bindParam(':email', $email);
            $query->execute();

            $response->getBody()->write(json_encode(['message' => 'User updated successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $ex) {
            $response->getBody()->write(json_encode(["error" => $ex->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        try {
            $query = $this->db->prepare("DELETE FROM users WHERE id = $id");
            $query->execute();

            $response->getBody()->write(json_encode(['message' => 'User deleted successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $ex) {
            $response->getBody()->write(json_encode(["error" => $ex->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
