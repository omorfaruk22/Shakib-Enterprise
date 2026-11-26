<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use Throwable;
use RuntimeException;

final class BookingService
{
    /** @var array<string, array{type:string,label:string,slug:string,date_required:bool}> */
    private const TYPES = [
        'air-ticket' => ['type' => 'air_ticket', 'label' => 'Air ticket inquiry', 'slug' => 'air-ticket', 'date_required' => true],
        'ship-ticket' => ['type' => 'ship_ticket', 'label' => 'Ship ticket inquiry', 'slug' => 'ship-ticket', 'date_required' => true],
        'bus-ticket' => ['type' => 'bus_ticket', 'label' => 'Bus ticket inquiry', 'slug' => 'bus-ticket', 'date_required' => true],
        'tour-travel' => ['type' => 'tour_package', 'label' => 'Tour inquiry', 'slug' => 'tour-travel', 'date_required' => false],
        'holiday-package' => ['type' => 'holiday_package', 'label' => 'Holiday package inquiry', 'slug' => 'holiday-package', 'date_required' => false],
    ];

    public static function definitions(): array
    {
        return self::TYPES;
    }

    public static function definition(string $slug): ?array
    {
        return self::TYPES[$slug] ?? null;
    }

    /** @return array{data: array<string, mixed>, errors: array<string, string>} */
    public function validate(array $input, array $definition): array
    {
        $errors = [];
        $name = $this->text($input, 'full_name');
        $phone = $this->text($input, 'phone');
        $email = strtolower($this->text($input, 'email'));
        $departure = $this->text($input, 'departure_location');
        $destination = $this->text($input, 'destination');
        $journeyDate = $this->text($input, 'journey_date');
        $returnDate = $this->text($input, 'return_date');
        $passengerCountRaw = $this->text($input, 'passenger_count');
        $passengerType = $this->text($input, 'passenger_type');
        $travelClass = $this->text($input, 'travel_class');
        $message = $this->text($input, 'customer_message');
        $packageSlug = $this->text($input, 'package_slug');
        $shipRouteRaw = $this->text($input, 'ship_route_id');
        $shipRouteId = null;

        foreach ([
            'full_name' => [$name, 160, 'Your name must be 160 characters or fewer.'],
            'phone' => [$phone, 40, 'Your phone number must be 40 characters or fewer.'],
            'departure_location' => [$departure, 180, 'Your departure location must be 180 characters or fewer.'],
            'destination' => [$destination, 180, 'Your destination must be 180 characters or fewer.'],
            'return_date' => [$returnDate, 10, 'The return date is invalid.'],
            'passenger_type' => [$passengerType, 80, 'Traveler details must be 80 characters or fewer.'],
            'travel_class' => [$travelClass, 80, 'Travel class must be 80 characters or fewer.'],
        ] as $field => [$value, $maximum, $messageForLength]) {
            if ($this->charLength($value) > $maximum) {
                $errors[$field] = $messageForLength;
            }
        }
        if ($this->charLength($journeyDate) > 10) {
            $errors['journey_date'] = 'The journey date is invalid.';
        }
        if ($name === '' || $this->charLength($name) < 2) {
            $errors['full_name'] = 'Please enter your name (at least 2 characters).';
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($phone === '' || preg_match('/^\+?[0-9() .-]+$/D', $phone) !== 1 || strlen($digits) < 7 || strlen($digits) > 15) {
            $errors['phone'] = 'Enter a valid phone number with 7 to 15 digits.';
        }
        if ($email !== '' && (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
            $errors['email'] = 'Enter a valid email address or leave this field blank.';
        }
        if (in_array($definition['slug'], ['air-ticket', 'bus-ticket'], true)) {
            if ($departure === '') {
                $errors['departure_location'] = 'Enter your departure city.';
            }
            if ($destination === '') {
                $errors['destination'] = 'Enter your destination.';
            }
        }
        if ($definition['slug'] === 'ship-ticket') {
            if ($shipRouteId === null && $departure === '') {
                $errors['departure_location'] = 'Enter your origin port or choose a published ship route.';
            }
            if ($shipRouteId === null && $destination === '') {
                $errors['destination'] = 'Enter your destination port or choose a published ship route.';
            }
            if ($departure !== '' && $destination !== '' && strcasecmp($departure, $destination) === 0) {
                $errors['destination'] = 'Origin and destination must be different.';
            }
        }
        if ($definition['date_required'] && !$this->validDate($journeyDate)) {
            $errors['journey_date'] = 'Choose a valid journey date from today onward.';
        } elseif (!$definition['date_required'] && $journeyDate !== '' && !$this->validDate($journeyDate)) {
            $errors['journey_date'] = 'Choose a valid travel date from today onward, or leave it blank.';
        }
        if ($returnDate !== '' && !$this->validDate($returnDate)) {
            $errors['return_date'] = 'Enter a valid return date.';
        } elseif ($returnDate !== '' && $journeyDate !== '' && $this->validDate($journeyDate) && $returnDate < $journeyDate) {
            $errors['return_date'] = 'Return date must be on or after the journey date.';
        }
        $passengerCount = filter_var($passengerCountRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        if ($passengerCount === false) {
            $errors['passenger_count'] = 'Enter a traveler count between 1 and 50.';
        }
        if ($this->charLength($message) > 4000) {
            $errors['customer_message'] = 'Please keep extra details under 4,000 characters.';
        }
        if ($this->charLength($packageSlug) > 180 || ($packageSlug !== '' && preg_match('/^[a-z0-9-]+$/D', $packageSlug) !== 1)) {
            $errors['package_slug'] = 'Choose a valid published package.';
        }
        if ($packageSlug !== '' && !in_array($definition['slug'], ['tour-travel', 'holiday-package'], true)) {
            $errors['package_slug'] = 'Only tour and holiday inquiries can include a package.';
        }
        if ($shipRouteRaw !== '') {
            $shipRouteId = filter_var($shipRouteRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($shipRouteId === false || $definition['slug'] !== 'ship-ticket') {
                $errors['ship_route_id'] = 'Choose a valid active ship route.';
                $shipRouteId = null;
            }
        }
        $normalizedPhone = $digits;
        if (str_starts_with($normalizedPhone, '00')) {
            $normalizedPhone = substr($normalizedPhone, 2);
        } elseif (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '0')) {
            $normalizedPhone = '880' . substr($normalizedPhone, 1);
        }
        $data = [
            'full_name' => $name,
            'phone' => $phone,
            'phone_normalized' => $normalizedPhone,
            'email' => $email !== '' ? $email : null,
            'departure_location' => $departure !== '' ? $departure : null,
            'destination' => $destination !== '' ? $destination : null,
            'journey_date' => $journeyDate !== '' ? $journeyDate : null,
            'return_date' => $returnDate !== '' ? $returnDate : null,
            'passenger_count' => $passengerCount === false ? null : $passengerCount,
            'passenger_type' => $passengerType !== '' ? $passengerType : null,
            'travel_class' => $travelClass !== '' ? $travelClass : null,
            'customer_message' => $message !== '' ? $message : null,
            'package_slug' => $packageSlug !== '' ? $packageSlug : null,
            'ship_route_id' => $shipRouteId,
        ];

        return ['data' => $data, 'errors' => $errors];
    }

    /** @return array{reference:string, duplicate:bool} */
    public function store(array $data, array $definition, string $submissionToken): array
    {
        $database = \App\Database\Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $tokenHash = hash('sha256', $submissionToken);
        $database->beginTransaction();
        try {
            $existing = $database->prepare('SELECT booking_reference FROM bookings WHERE submission_token_hash = ? LIMIT 1');
            $existing->execute([$tokenHash]);
            $existingReference = $existing->fetchColumn();
            if (is_string($existingReference)) {
                $database->commit();
                return ['reference' => $existingReference, 'duplicate' => true];
            }

            $departure = $data['departure_location'];
            $destination = $data['destination'];
            $shipRouteId = null;
            if ($definition['slug'] === 'ship-ticket' && $data['ship_route_id'] !== null) {
                $routeQuery = $database->prepare('SELECT id, origin, destination FROM ship_routes WHERE id = ? AND is_active = 1 LIMIT 1');
                $routeQuery->execute([$data['ship_route_id']]);
                $route = $routeQuery->fetch();
                if (!is_array($route)) {
                    throw new BookingSubmissionException(['ship_route_id' => 'That ship route is no longer available. Please choose another route.']);
                }
                $departure = (string) $route['origin'];
                $destination = (string) $route['destination'];
                $shipRouteId = (int) $route['id'];
            }

            $serviceQuery = $database->prepare('SELECT id FROM services WHERE slug = ? AND is_active = 1 LIMIT 1');
            $serviceQuery->execute([$definition['slug']]);
            $serviceValue = $serviceQuery->fetchColumn();
            $serviceId = is_numeric($serviceValue) ? (int) $serviceValue : null;

            $packageId = null;
            if ($data['package_slug'] !== null) {
                $expectedPackageType = $definition['type'] === 'tour_package' ? 'tour' : 'holiday';
                $packageQuery = $database->prepare('SELECT id FROM packages WHERE slug = ? AND package_type = ? AND is_active = 1 LIMIT 1');
                $packageQuery->execute([$data['package_slug'], $expectedPackageType]);
                $packageValue = $packageQuery->fetchColumn();
                if (!is_numeric($packageValue)) {
                    throw new BookingSubmissionException(['package_slug' => 'That package is no longer available. Please choose another option.']);
                }
                $packageId = (int) $packageValue;
            }

            $customerQuery = $database->prepare('SELECT id FROM customers WHERE phone_normalized = ? ORDER BY id ASC LIMIT 1 FOR UPDATE');
            $customerQuery->execute([$data['phone_normalized']]);
            $customerId = $customerQuery->fetchColumn();
            if (is_numeric($customerId)) {
                $customerUpdate = $database->prepare(
                    'UPDATE customers SET full_name = ?, phone = ?, email = COALESCE(?, email) WHERE id = ?'
                );
                $customerUpdate->execute([$data['full_name'], $data['phone'], $data['email'], (int) $customerId]);
                $customerId = (int) $customerId;
            } else {
                $customerInsert = $database->prepare(
                    'INSERT INTO customers (full_name, phone, phone_normalized, email) VALUES (?, ?, ?, ?)'
                );
                $customerInsert->execute([$data['full_name'], $data['phone'], $data['phone_normalized'], $data['email']]);
                $customerId = (int) $database->lastInsertId();
            }

            $reference = null;
            $referenceCheck = $database->prepare('SELECT 1 FROM bookings WHERE booking_reference = ? LIMIT 1');
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $candidate = $this->newReference();
                $referenceCheck->execute([$candidate]);
                if ($referenceCheck->fetchColumn() === false) {
                    $reference = $candidate;
                    break;
                }
            }
            if ($reference === null) {
                throw new RuntimeException('A unique inquiry reference could not be generated.');
            }
            $bookingInsert = $database->prepare(
                'INSERT INTO bookings
                    (booking_reference, submission_token_hash, customer_id, service_type, service_id, package_id, ship_route_id,
                     departure_location, destination, journey_date, return_date, passenger_count, passenger_type, travel_class, customer_message)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $bookingInsert->execute([
                $reference,
                $tokenHash,
                $customerId,
                $definition['type'],
                $serviceId,
                $packageId,
                $shipRouteId,
                $departure,
                $destination,
                $data['journey_date'],
                $data['return_date'],
                $data['passenger_count'],
                $data['passenger_type'],
                $data['travel_class'],
                $data['customer_message'],
            ]);

            $statusHistory = $database->prepare(
                'INSERT INTO booking_status_history (booking_id, admin_id, old_status, new_status, note)
                 VALUES (?, NULL, NULL, ?, ?)'
            );
            $statusHistory->execute([(int) $database->lastInsertId(), 'pending', 'Inquiry received']);

            $database->commit();
            return ['reference' => $reference, 'duplicate' => false];
        } catch (BookingSubmissionException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            throw $exception;
        } catch (PDOException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            if ($exception->getCode() === '23000') {
                $lookup = $database->prepare('SELECT booking_reference FROM bookings WHERE submission_token_hash = ? LIMIT 1');
                $lookup->execute([$tokenHash]);
                $duplicateReference = $lookup->fetchColumn();
                if (is_string($duplicateReference)) {
                    return ['reference' => $duplicateReference, 'duplicate' => true];
                }
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            throw $exception;
        }
    }

    public function findPublicConfirmation(string $reference): ?array
    {
        if (preg_match('/^SE-[0-9]{6}-[A-F0-9]{16}$/D', $reference) !== 1) {
            return null;
        }
        $database = \App\Database\Connection::fromConfig(require BASE_PATH . '/config/database.php');
        $query = $database->prepare(
            'SELECT b.booking_reference, b.service_type, b.status, b.created_at, s.name AS service_name
             FROM bookings AS b LEFT JOIN services AS s ON s.id = b.service_id
             WHERE b.booking_reference = ? LIMIT 1'
        );
        $query->execute([$reference]);
        $row = $query->fetch();
        if (!is_array($row)) {
            return null;
        }
        return [
            'reference' => (string) $row['booking_reference'],
            'service_name' => (string) ($row['service_name'] ?? $this->serviceLabel((string) $row['service_type'])),
            'created_at' => (string) $row['created_at'],
        ];
    }

    private function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value)) {
            return '';
        }
        return trim($value);
    }

    private function charLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }
        $count = preg_match_all('/./us', $value, $characters);
        return $count === false ? strlen($value) : $count;
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value
            && $value >= date('Y-m-d');
    }

    private function newReference(): string
    {
        return 'SE-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(8)));
    }

    private function serviceLabel(string $serviceType): string
    {
        foreach (self::TYPES as $definition) {
            if ($definition['type'] === $serviceType) {
                return $definition['label'];
            }
        }
        return 'Travel inquiry';
    }
}

final class BookingSubmissionException extends RuntimeException
{
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Booking submission data is no longer available.');
    }
}
