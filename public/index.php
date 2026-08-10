<?php
use Data\Database;
use App\Models\Order;
use App\Models\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente se houver arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Inicializa o Eloquent ORM e conexão com PostgreSQL
Database::bootstrap();

$app = AppFactory::create();

// Middleware para JSON Body Parser
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Health Check
$app->get('/health', function (Request $request, Response $response) {
    try {
        Illuminate\Database\Capsule\Manager::connection()->getPdo();
        $payload = json_encode(['status' => 'ok', 'database' => 'ok']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Exception $e) {
        return $response->withStatus(503);
    }
});

// Criar Pedido
$app->post('/orders', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $customer = $data['customer'] ?? null;

    if (!$customer) {
        return $response->withStatus(400);
    }

    $order = Order::create([
        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'customer' => $customer,
        'status' => 'Created',
        'created_at' => gmdate('Y-m-d H:i:s')
    ]);

    $payload = json_encode($order);
    $response->getBody()->write($payload);
    
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

// Listar Pedidos
$app->get('/orders', function (Request $request, Response $response) {
    $orders = Order::with('items')->get();
    $payload = json_encode($orders);
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// Buscar Pedido por ID
$app->get('/orders/{id}', function (Request $request, Response $response, array $args) {
    $order = Order::with('items')->find($args['id']);

    if (!$order) {
        $payload = json_encode(['detail' => 'Pedido não encontrado']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $payload = json_encode($order);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// Adicionar Item ao Pedido
$app->post('/orders/{id}/items', function (Request $request, Response $response, array $args) {
    $order = Order::find($args['id']);
    if (!$order) {
        $payload = json_encode(['detail' => 'Pedido não encontrado']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $data = $request->getParsedBody();
    
    $item = Item::create([
        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'order_id' => $args['id'],
        'sku' => $data['sku'] ?? '',
        'description' => $data['description'] ?? '',
        'quantity' => $data['quantity'] ?? 0
    ]);

    $payload = json_encode($item);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Listar Itens do Pedido
$app->get('/orders/{id}/items', function (Request $request, Response $response, array $args) {
    $order = Order::with('items')->find($args['id']);
    if (!$order) {
        $payload = json_encode(['detail' => 'Pedido não encontrado']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $payload = json_encode($order->items);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// Cancelar Pedido (Delete lógico)
$app->delete('/orders/{id}', function (Request $request, Response $response, array $args) {
    $order = Order::find($args['id']);
    if (!$order) {
        $payload = json_encode(['detail' => 'Pedido não encontrado']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $order->status = 'cancelled';
    $order->save();

    return $response->withStatus(204);
});

$app->run();