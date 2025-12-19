<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUser(string $role = 'it_staff'): User {
    return User::factory()->create([
        'role' => $role,
        'email_verified_at' => now(),
        'account_status' => 'active',
    ]);
}

function makeTickets(int $count = 12): array {
    $tickets = [];
    for ($i = 1; $i <= $count; $i++) {
        $tickets[] = Ticket::create([
            'holder_name' => 'Holder '.$i,
            'holder_email' => 'holder'.$i.'@example.com',
            'ticket_number' => 'INC'.str_pad((string)$i, 12, '0', STR_PAD_LEFT),
            'issue' => 'Test Issue',
            'status' => 'pending approval',
        ]);
    }
    return $tickets;
}

it('updates ticket status for id 11 via web route', function () {
    $user = makeUser('it_staff');
    $this->actingAs($user);

    $tickets = makeTickets(12);
    $target = $tickets[10]; // index 10 => id 11 in creation order

    $response = $this->put('/tickets/'.$target->id, [
        'status' => 'queued',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Ticket status updated successfully']);

    $target->refresh();
    expect($target->status)->toBe('queued');
});

it('updates ticket status for id 12 via web route', function () {
    $user = makeUser('it_staff');
    $this->actingAs($user);

    $tickets = makeTickets(12);
    $target = $tickets[11]; // index 11 => id 12

    $response = $this->put('/tickets/'.$target->id, [
        'status' => 'queued',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Ticket status updated successfully']);

    $target->refresh();
    expect($target->status)->toBe('queued');
});
