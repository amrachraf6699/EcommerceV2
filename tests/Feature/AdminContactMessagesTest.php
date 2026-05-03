<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminContactMessagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAuthorizationSeeder::class);

        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->assignRole('super-admin');
    }

    public function test_admin_can_view_reply_to_and_delete_contact_messages(): void
    {
        Mail::fake();

        $message = ContactMessage::query()->create([
            'name' => 'Visitor',
            'phone' => null,
            'email' => 'visitor@example.com',
            'subject' => 'Question',
            'message' => 'Hello from storefront.',
            'is_replied' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSeeText('visitor@example.com')
            ->assertSeeText('بانتظار الرد');

        $this->actingAs($this->superAdmin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk()
            ->assertSeeText('Hello from storefront.');

        $this->actingAs($this->superAdmin)
            ->post(route('admin.contact-messages.reply', $message), [
                'subject' => 'Re: Question',
                'message' => '<p>Thanks for reaching out.</p>',
            ])->assertRedirect(route('admin.contact-messages.show', $message))
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessageReplyMail::class, function (ContactMessageReplyMail $mail) use ($message): bool {
            return $mail->hasTo($message->email)
                && $mail->subjectLine === 'Re: Question'
                && $mail->replyBody === '<p>Thanks for reaching out.</p>';
        });

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'is_replied' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSeeText('تم الرد');

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.contact-messages.destroy', $message))
            ->assertRedirect(route('admin.contact-messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', [
            'id' => $message->id,
        ]);
    }
}
