namespace CloudApplication;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiTest extends TestCase
{
    private Client $client;
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define a URL base da aplicação para os testes
        $this->baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false, // Permite capturar códigos de status diferentes de 2xx
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Equivalente ao ajuste de propriedades de conexão e ddl-auto.
     * Em PHP, a configuração do banco e migrações geralmente é feita via variáveis de ambiente 
     * no arquivo phpunit.xml ou bootstrap do framework (ex: Laravel/Symfony).
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $dbUrl = getenv('DATABASE_URL');
        if (empty($dbUrl)) {
            $dbUrl = 'pgsql://postgres:postgres@localhost:5432/orders';
        }

        // Exemplo de definição em tempo de execução para o ambiente de testes
        $_ENV['DB_CONNECTION'] = 'pgsql';
        $_ENV['DB_URL'] = $dbUrl;
    }

    public function testHealth(): void
    {
        $response = $this->client->get('/health');

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertNotNull($json);
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('database', $json);
    }

    public function testCreateOrder(): void
    {
        $newOrder = [
            'customer' => 'Maria'
        ];

        $response = $this->client->post('/orders', [
            'json' => $newOrder
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $order = json_decode($response->getBody()->getContents(), true);

        $this->assertNotNull($order);
        $this->assertEquals('Maria', $order['customer'] ?? null);
        $this->assertEquals('Created', $order['status'] ?? null);
        $this->assertNotNull($order['id'] ?? null);
    }
}