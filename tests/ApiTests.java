package com.cloudapplication;

import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.boot.test.web.client.TestRestTemplate;
import org.springframework.http.*;
import org.springframework.test.context.DynamicPropertyRegistry;
import org.springframework.test.context.DynamicPropertySource;

import java.util.Map;

import static org.assertj.core.api.Assertions.assertThat;

@SpringBootTest(webEnvironment = SpringBootTest.WebEnvironment.RANDOM_PORT)
class ApiTests {

    @Autowired
    private TestRestTemplate restTemplate;

    // Configura dinamicamente a conexão com o PostgreSQL para os testes, 
    // equivalendo ao ajuste da string de conexão no WebHost.
    @DynamicPropertySource
    static void configureProperties(DynamicPropertyRegistry registry) {
        String dbUrl = System.getenv("DATABASE_URL");
        if (dbUrl == null || dbUrl.isEmpty()) {
            dbUrl = "jdbc:postgresql://localhost:5432/orders";
        } else {
            // Nota: No Spring, URLs do JDBC geralmente começam com jdbc:postgresql://
            // Dependendo de como a string de ambiente vem, pode ser necessário ajustar o prefixo.
        }

        registry.add("spring.datasource.url", () -> dbUrl);
        registry.add("spring.datasource.username", () -> "postgres");
        registry.add("spring.datasource.password", () -> "postgres");
        registry.add("spring.jpa.hibernate.ddl-auto", () -> "create-drop"); // Equivalente ao EnsureCreated()
    }

    @Test
    void testHealth() {
        ResponseEntity<Map> response = restTemplate.getForEntity("/health", Map.class);
        
        assertThat(response.getStatusCode()).isEqualTo(HttpStatus.OK);
        
        Map<String, String> json = response.getBody();
        assertThat(json).isNotNull();
        assertThat(json).containsKey("status");
        assertThat(json).containsKey("database");
    }

    @Test
    void testCreateOrder() {
        Map<String, Object> newOrder = Map.of("customer", "Maria");
        
        ResponseEntity<Order> response = restTemplate.postForEntity("/orders", newOrder, Order.class);
        
        assertThat(response.getStatusCode()).isEqualTo(HttpStatus.CREATED);
        
        Order order = response.getBody();
        assertThat(order).isNotNull();
        assertThat(order.getCustomer()).isEqualTo("Maria");
        assertThat(order.getStatus()).isEqualTo("Created");
        assertThat(order.getId()).isNotNull();
    }
}