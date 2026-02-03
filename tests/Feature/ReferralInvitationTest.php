<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use LaravelPlus\Referral\Mail\ReferralInvitationMail;
use Tests\TestCase;

final class ReferralInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_send_invitation(): void
    {
        $response = $this->postJson('/referral/invite', [
            'email' => 'friend@example.com',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_send_invitation(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/referral/invite', [
            'email' => 'friend@example.com',
            'message' => 'Join us!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Invitation sent successfully.');

        Mail::assertSent(ReferralInvitationMail::class, fn (ReferralInvitationMail $mail) => $mail->hasTo('friend@example.com'));
    }

    public function test_invitation_requires_valid_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/referral/invite', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_invitation_email_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/referral/invite', []);

        $response->assertSessionHasErrors('email');
    }

    public function test_invitation_message_is_optional(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/referral/invite', [
            'email' => 'friend@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Invitation sent successfully.');
    }

    public function test_invitation_creates_referral_record(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post('/referral/invite', [
            'email' => 'friend@example.com',
        ]);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $user->id,
            'email' => 'friend@example.com',
            'channel' => 'email',
        ]);

        $this->assertDatabaseHas('referral_invitations', [
            'email' => 'friend@example.com',
        ]);
    }

    public function test_invitation_respects_daily_limit(): void
    {
        Mail::fake();
        config(['referral.invitation.max_per_day' => 2]);

        $user = User::factory()->create();

        // Send 2 invitations (the limit)
        $this->actingAs($user)->post('/referral/invite', ['email' => 'a@example.com']);
        $this->actingAs($user)->post('/referral/invite', ['email' => 'b@example.com']);

        // Third should fail
        $response = $this->actingAs($user)->post('/referral/invite', ['email' => 'c@example.com']);

        $response->assertSessionHasErrors('email');
    }
}
