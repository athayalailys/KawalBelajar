<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\LearningMaterial;
use App\Models\QuestionBank;
use App\Models\TutorClassSessionContent;
use App\Models\TutorPackageRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesBookablePackages;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBookablePackages;

    public function test_checkout_001_creates_pending_order_and_xendit_invoice(): void
    {
        [$studentUser] = $this->marketplace();
        $this->fakeXendit('invoice-checkout');

        $response = $this->actingAs($studentUser)
            ->postJson('/api/student/booking/checkout', $this->checkoutPayload())
            ->assertCreated()
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonPath('data.payment_url', 'https://pay.example.test/invoice');

        $this->assertDatabaseHas('orders', [
            'order_id' => $response->json('data.order_id'),
            'payment_status' => 'pending',
            'xendit_invoice_id' => 'invoice-checkout',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $response->json('data.order_id'),
            'payment_status' => 'pending',
        ]);
    }

    public function test_pay_001_settled_webhook_confirms_order_and_schedules_four_weekly_sessions(): void
    {
        [$studentUser] = $this->marketplace();
        $this->fakeXendit('invoice-settled');
        $checkout = $this->actingAs($studentUser)
            ->postJson('/api/student/booking/checkout', $this->checkoutPayload(['session_package' => 4, 'format' => 'host', 'target_capacity' => 2]))
            ->assertCreated();
        $order = Order::findOrFail($checkout->json('data.order_id'));

        $this->postJson('/api/payments/xendit/webhook', $this->webhookPayload($order, 'SETTLED'), [
            'x-callback-token' => 'test-callback-token',
        ])->assertOk();

        $this->assertDatabaseHas('orders', ['order_id' => $order->order_id, 'payment_status' => 'settled']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->order_id, 'payment_status' => 'settled']);
        $this->assertNotEmpty($checkout->json('data.squad_code'));
        $this->assertDatabaseHas('squads', ['squad_code' => $checkout->json('data.squad_code')]);

        $sessions = LearningSession::query()->where('order_id', $order->order_id)->with('schedule')->orderBy('scheduled_at')->get();
        $this->assertCount(4, $sessions);
        $this->assertEquals(7, abs($sessions[1]->scheduled_at->diffInDays($sessions[0]->scheduled_at)));
        $this->assertEquals(7, abs($sessions[2]->scheduled_at->diffInDays($sessions[1]->scheduled_at)));
        $this->assertEquals(7, abs($sessions[3]->scheduled_at->diffInDays($sessions[2]->scheduled_at)));
    }

    public function test_pay_002_expired_webhook_marks_order_and_payment_expired(): void
    {
        [$studentUser] = $this->marketplace();
        $this->fakeXendit('invoice-expired');
        $checkout = $this->actingAs($studentUser)
            ->postJson('/api/student/booking/checkout', $this->checkoutPayload())
            ->assertCreated();
        $order = Order::findOrFail($checkout->json('data.order_id'));

        $this->postJson('/api/payments/xendit/webhook', $this->webhookPayload($order, 'EXPIRED'), [
            'x-callback-token' => 'test-callback-token',
        ])->assertOk();

        $this->assertDatabaseHas('orders', ['order_id' => $order->order_id, 'payment_status' => 'expired']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->order_id, 'payment_status' => 'expired']);
        $this->assertDatabaseMissing('learning_sessions', ['order_id' => $order->order_id]);
    }

    private function marketplace(): array
    {
        config(['services.xendit.secret_key' => 'test-secret', 'services.xendit.webhook_token' => 'test-callback-token']);

        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->user_id,
            'education_level' => 'SMP',
        ]);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
            'approved_levels' => ['SMP'],
        ]);
        $package = ModulePackage::create([
            'teacher_id' => $teacher->teacher_id,
            'package_name' => 'Paket Webhook',
            'grade_level' => 'SMP',
            'base_price' => 50000,
            'supported_session_packages' => '1,4',
            'is_published' => true,
            'is_open' => true,
            'is_active' => true,
        ]);
        $this->makePackageReadyForEnrollment($teacher, $package);
        TutorPackageRate::create([
            'package_id' => $package->package_id,
            'teacher_level' => 'junior',
            'learning_method' => 'online_zoom',
            'category_rate' => 50000,
        ]);
        $slot = TeacherAvailabilitySlot::create([
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => now()->addDay()->format('l'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'learning_method' => 'online_zoom',
            'is_active' => true,
        ]);

        return [$studentUser, $student, $teacher, $package, $slot];
    }

    private function checkoutPayload(array $overrides = []): array
    {
        $teacher = Teacher::firstOrFail();
        $package = ModulePackage::firstOrFail();
        $slot = TeacherAvailabilitySlot::firstOrFail();

        return array_merge([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'format' => 'solo',
            'selected_slot_id' => $slot->slot_id,
        ], $overrides);
    }

    private function fakeXendit(string $invoiceId): void
    {
        Http::fake(['https://api.xendit.co/v2/invoices' => Http::response([
            'id' => $invoiceId,
            'invoice_url' => 'https://pay.example.test/invoice',
        ], 200)]);
    }

    private function webhookPayload(Order $order, string $status): array
    {
        return [
            'id' => $order->xendit_invoice_id,
            'external_id' => $order->invoice_number,
            'status' => $status,
            'amount' => $order->total_amount,
        ];
    }
}
