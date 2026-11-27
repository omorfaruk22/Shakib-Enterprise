<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Validator;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;

class BookingService
{
    /**
     * Create a booking inquiry transactionally.
     * Returns booking reference on success.
     */
    public static function createInquiry(array $input): string
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $customerId = Customer::findOrCreate(
                $input['customer_name'],
                $input['phone'],
                $input['email'] ?? null,
                $input['address'] ?? null
            );
            $service = Service::findBySlug($input['service_slug'] ?? '');
            $ref = Booking::generateReference();
            // Ensure uniqueness (retry once on collision)
            $attempts = 0;
            while (Booking::findByReference($ref) && $attempts < 5) {
                $ref = Booking::generateReference();
                $attempts++;
            }
            $bookingId = Booking::create([
                'booking_reference' => $ref,
                'customer_id' => $customerId,
                'service_type' => $input['service_type'],
                'service_id' => $service['id'] ?? null,
                'package_type' => $input['package_type'] ?? null,
                'package_id' => $input['package_id'] ?? null,
                'ship_route_id' => $input['ship_route_id'] ?? null,
                'departure_location' => $input['departure_location'] ?? null,
                'destination' => $input['destination'] ?? null,
                'journey_date' => $input['journey_date'] ?? null,
                'return_date' => $input['return_date'] ?? null,
                'passenger_count' => $input['passenger_count'] ?? 1,
                'passenger_type' => $input['passenger_type'] ?? null,
                'travel_class' => $input['travel_class'] ?? null,
                'customer_name' => $input['customer_name'],
                'phone' => $input['phone'],
                'email' => $input['email'] ?? null,
                'address' => $input['address'] ?? null,
                'notes' => $input['notes'] ?? null,
                'status' => 'pending',
            ]);
            $pdo->prepare('INSERT INTO booking_status_history (booking_id, old_status, new_status, note) VALUES (?, NULL, "pending", "Inquiry submitted")')
                ->execute([$bookingId]);
            $pdo->commit();
            return $ref;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function validateCommon(array $data, array $required = ['customer_name','phone','journey_date','passenger_count']): array
    {
        $errors = Validator::required($data, $required);
        if (!empty($data['phone']) && !Validator::phone($data['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }
        if (!empty($data['email']) && !Validator::email($data['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (!empty($data['journey_date']) && !Validator::date($data['journey_date'])) {
            $errors['journey_date'] = 'Please select a valid journey date.';
        }
        if (!empty($data['return_date']) && !Validator::date($data['return_date'])) {
            $errors['return_date'] = 'Please select a valid return date.';
        }
        if (!empty($data['passenger_count']) && ((int)$data['passenger_count'] < 1 || (int)$data['passenger_count'] > 50)) {
            $errors['passenger_count'] = 'Passenger count must be between 1 and 50.';
        }
        return $errors;
    }
}
