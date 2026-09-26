<?php

namespace Database\Seeders;

use App\Enums\BookingType;
use App\Enums\ClinicStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\QueueState;
use App\Models\Review;
use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $super = $this->user('ArogyaQ Admin', '9999999999', 'admin@arogyaq.test', 'Admin@123', UserRole::SuperAdmin);

        $shantiAdmin = $this->user('Hetal Shah', '9888888888', 'shanti@arogyaq.test', 'Clinic@123', UserRole::ClinicAdmin);
        $shanti = Clinic::query()->create([
            'admin_id' => $shantiAdmin->id,
            'name' => 'Shanti Multispeciality Clinic',
            'slug' => 'shanti-multispeciality',
            'specialty' => 'General Medicine',
            'description' => 'A Navrangpura OPD desk that replaced paper tokens with a living board. General medicine in the morning, skin clinic in the evening.',
            'address' => '12, Vrundavan Cross Road, Navrangpura',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'latitude' => 23.0369000,
            'longitude' => 72.5611000,
            'phone' => '9888888888',
            'email' => 'shanti@arogyaq.test',
            'status' => ClinicStatus::Approved,
            'services' => ['OPD', 'Vaccination', 'Lab collection'],
            'cancel_cutoff_minutes' => 30,
            'refund_percent' => 100,
            'approved_at' => now()->subMonths(2),
        ]);
        $shantiAdmin->update(['clinic_id' => $shanti->id]);

        $childAdmin = $this->user('Nirali Joshi', '9888888887', 'littlesteps@arogyaq.test', 'Clinic@123', UserRole::ClinicAdmin);
        $child = Clinic::query()->create([
            'admin_id' => $childAdmin->id,
            'name' => 'Little Steps Child Clinic',
            'slug' => 'little-steps',
            'specialty' => 'Pediatrics',
            'description' => 'Ellisbridge clinic for fever, vaccines, and the school-term cough.',
            'address' => '4, Law Garden Road, Ellisbridge',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380006',
            'latitude' => 23.0225000,
            'longitude' => 72.5714000,
            'phone' => '9888888887',
            'status' => ClinicStatus::Approved,
            'services' => ['Pediatrics', 'Vaccination'],
            'cancel_cutoff_minutes' => 30,
            'refund_percent' => 80,
            'approved_at' => now()->subMonth(),
        ]);
        $childAdmin->update(['clinic_id' => $child->id]);

        $lotusAdmin = $this->user('Karan Mehta', '9888888889', 'lotus@arogyaq.test', 'Clinic@123', UserRole::ClinicAdmin);
        $lotus = Clinic::query()->create([
            'admin_id' => $lotusAdmin->id,
            'name' => 'Lotus Dental Studio',
            'slug' => 'lotus-dental',
            'specialty' => 'Dental',
            'description' => 'Satellite dental studio waiting for platform approval.',
            'address' => '88, Jodhpur Cross Road, Satellite',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380015',
            'latitude' => 23.0300000,
            'longitude' => 72.5110000,
            'phone' => '9888888889',
            'status' => ClinicStatus::Pending,
            'services' => ['Dental'],
            'cancel_cutoff_minutes' => 30,
            'refund_percent' => 100,
        ]);
        $lotusAdmin->update(['clinic_id' => $lotus->id]);

        $meeraUser = $this->user('Meera Shah', '9777777771', 'meera@arogyaq.test', 'Doctor@123', UserRole::Doctor, $shanti->id);
        $meera = $this->doctor($shanti, $meeraUser, 'General Medicine', 'MBBS, MD', 11, 400, 'Room 1', true);

        $kavyaUser = $this->user('Kavya Desai', '9777777773', 'kavya@arogyaq.test', 'Doctor@123', UserRole::Doctor, $shanti->id);
        $kavya = $this->doctor($shanti, $kavyaUser, 'Dermatology', 'MBBS, DVD', 7, 600, 'Room 3', false);
        DoctorLeave::query()->create([
            'doctor_id' => $kavya->id,
            'leave_date' => now()->next(Carbon::MONDAY)->toDateString(),
            'reason' => 'Conference',
        ]);

        $aaravUser = $this->user('Aarav Patel', '9777777772', 'aarav@arogyaq.test', 'Doctor@123', UserRole::Doctor, $child->id);
        $aarav = $this->doctor($child, $aaravUser, 'Pediatrics', 'MBBS, DCH', 9, 500, 'Room 2', false);

        $this->user('Rina Desai', '9666666666', 'desk@arogyaq.test', 'Desk@123', UserRole::Receptionist, $shanti->id);

        $riya = $this->user('Riya Mehta', '9555555555', 'riya@arogyaq.test', 'Patient@123', UserRole::Patient);
        $jay = $this->user('Jay Trivedi', '9555555556', 'jay@arogyaq.test', 'Patient@123', UserRole::Patient);
        $kiran = $this->user('Kiran Solanki', '9555555557', null, 'Patient@123', UserRole::Patient);
        $nidhi = $this->user('Nidhi Joshi', '9555555558', null, 'Patient@123', UserRole::Patient);
        $aman = $this->user('Aman Rao', '9555555559', null, 'Patient@123', UserRole::Patient);
        $piya = $this->user('Piya Shah', '9555555560', null, 'Patient@123', UserRole::Patient);
        $dev = $this->user('Dev Parmar', '9555555561', null, 'Patient@123', UserRole::Patient);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $this->visit($meera, $kiran, 1, $today, TokenStatus::Completed, BookingType::WalkIn, 9, PaymentStatus::Paid, PaymentMode::Cash);
        $jayVisit = $this->visit($meera, $jay, 2, $today, TokenStatus::Completed, BookingType::Online, 10, PaymentStatus::Paid, PaymentMode::Online);
        $this->visit($meera, $nidhi, 3, $today, TokenStatus::InProgress, BookingType::Online, 11, PaymentStatus::Paid, PaymentMode::Online);
        $this->visit($meera, $riya, 4, $today, TokenStatus::Booked, BookingType::Online, 11, PaymentStatus::Pending, PaymentMode::Online, 'Sore throat since last night');
        $this->visit($meera, $aman, 5, $today, TokenStatus::Booked, BookingType::Online, 17, PaymentStatus::Pending, PaymentMode::Cash);
        $this->visit($meera, $piya, 6, $today, TokenStatus::Booked, BookingType::Online, 17, PaymentStatus::Paid, PaymentMode::Online);
        $this->visit($meera, $dev, 7, $today, TokenStatus::Arrived, BookingType::WalkIn, 18, PaymentStatus::Paid, PaymentMode::Cash);
        $this->visit($meera, $jay, 8, $today, TokenStatus::Cancelled, BookingType::Online, 9, PaymentStatus::Failed, PaymentMode::Online);

        $past = $this->visit($meera, $riya, 1, $yesterday, TokenStatus::Completed, BookingType::Online, 16, PaymentStatus::Paid, PaymentMode::Online, 'Follow-up fever');
        Prescription::query()->create([
            'token_id' => $past->id,
            'doctor_id' => $meera->id,
            'patient_id' => $riya->id,
            'medicines' => [
                ['name' => 'Paracetamol', 'dosage' => '500 mg', 'duration' => '3 days', 'timing' => 'After food'],
                ['name' => 'Cetirizine', 'dosage' => '10 mg', 'duration' => '5 days', 'timing' => 'Night'],
            ],
            'notes' => 'Rest, fluids, return if fever crosses 102.',
            'follow_up_date' => now()->addDays(4)->toDateString(),
        ]);
        Review::query()->create([
            'token_id' => $past->id,
            'patient_id' => $riya->id,
            'doctor_id' => $meera->id,
            'clinic_id' => $shanti->id,
            'rating' => 5,
            'comment' => 'Reached exactly when the board said token 12. No waiting-room hour.',
        ]);
        Review::query()->create([
            'token_id' => $jayVisit->id,
            'patient_id' => $jay->id,
            'doctor_id' => $meera->id,
            'clinic_id' => $shanti->id,
            'rating' => 4,
            'comment' => 'Clear prescription, short consult.',
            'response' => 'Thank you Jay, see you if the cough stays.',
            'responded_at' => now(),
        ]);

        $this->visit($aarav, $piya, 1, $today, TokenStatus::InProgress, BookingType::Online, 10, PaymentStatus::Paid, PaymentMode::Cash);
        $this->visit($aarav, $dev, 2, $today, TokenStatus::Booked, BookingType::Online, 11, PaymentStatus::Pending, PaymentMode::Online);

        QueueState::query()->create([
            'doctor_id' => $meera->id,
            'clinic_id' => $shanti->id,
            'service_date' => $today,
            'current_token' => 3,
            'total_booked' => 8,
            'avg_consultation_minutes' => 12,
            'room' => 'Room 1',
            'doctor_name' => 'Meera Shah',
        ]);
        QueueState::query()->create([
            'doctor_id' => $aarav->id,
            'clinic_id' => $child->id,
            'service_date' => $today,
            'current_token' => 1,
            'total_booked' => 2,
            'avg_consultation_minutes' => 15,
            'room' => 'Room 2',
            'doctor_name' => 'Aarav Patel',
        ]);

        unset($super);
    }

    private function user(string $name, string $mobile, ?string $email, string $password, UserRole $role, ?int $clinicId = null): User
    {
        return User::query()->create([
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'language_pref' => 'en',
            'clinic_id' => $clinicId,
            'is_active' => true,
            'mobile_verified_at' => now(),
        ]);
    }

    private function doctor(Clinic $clinic, User $user, string $specialization, string $qualification, int $years, int $fee, string $room, bool $evening): Doctor
    {
        $doctor = Doctor::query()->create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'specialization' => $specialization,
            'qualification' => $qualification,
            'experience_years' => $years,
            'consultation_fee' => $fee,
            'room' => $room,
            'bio' => $specialization.' at '.$clinic->name.'.',
            'max_tokens_per_day' => 40,
            'is_active' => true,
        ]);

        foreach (range(1, 6) as $day) {
            DoctorSchedule::query()->create([
                'doctor_id' => $doctor->id,
                'day_of_week' => $day,
                'start_time' => '10:00',
                'end_time' => '13:00',
                'avg_consultation_minutes' => $specialization === 'Pediatrics' ? 15 : 12,
            ]);
            if ($evening) {
                DoctorSchedule::query()->create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '17:00',
                    'end_time' => '20:00',
                    'avg_consultation_minutes' => 12,
                ]);
            }
        }

        return $doctor;
    }

    private function visit(
        Doctor $doctor,
        User $patient,
        int $number,
        string $date,
        TokenStatus $status,
        BookingType $type,
        int $hour,
        PaymentStatus $payStatus,
        PaymentMode $mode,
        ?string $symptoms = null,
    ): Token {
        $token = Token::query()->create([
            'clinic_id' => $doctor->clinic_id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'token_number' => $number,
            'date' => $date,
            'status' => $status,
            'booking_type' => $type,
            'symptoms' => $symptoms,
            'estimated_wait_minutes' => max(0, $number - 1) * 12,
            'cancelled_at' => $status === TokenStatus::Cancelled ? now() : null,
        ]);
        $token->forceFill([
            'created_at' => Carbon::parse($date)->setTime($hour, 10),
            'updated_at' => Carbon::parse($date)->setTime($hour, 20),
        ])->save();

        Payment::query()->create([
            'token_id' => $token->id,
            'patient_id' => $patient->id,
            'clinic_id' => $doctor->clinic_id,
            'amount' => $doctor->consultation_fee,
            'mode' => $mode,
            'status' => $payStatus,
            'gateway' => $mode === PaymentMode::Cash ? 'cash' : 'demo',
            'transaction_id' => $payStatus === PaymentStatus::Paid ? 'DEMO-'.$token->id : null,
        ]);

        return $token;
    }
}
