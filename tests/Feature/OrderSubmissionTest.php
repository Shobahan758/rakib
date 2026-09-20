<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderSubmissionTest extends TestCase
{
    public function test_order_endpoint_rejects_invalid_data_with_english_messages(): void
    {
        $response = $this->postJson(route('orders.store'), [
            'name' => '',
            'phone' => '12345',
            'address' => 'Dhaka',
            'quantity' => 10000,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Enter your name.')
            ->assertJsonPath('errors.phone.0', 'Enter a valid Bangladeshi phone number.')
            ->assertJsonPath('errors.address.0', 'Provide a more detailed address.')
            ->assertJsonPath('errors.quantity.0', 'Choose a valid quantity.');
    }

    public function test_homepage_contains_the_laravel_order_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('orders.store', [], false))
            ->assertSee('name="_token"', false);
    }
}
