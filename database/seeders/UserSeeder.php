<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed 1 Admin + 125 dummy Employee records.
     */
    public function run(): void
    {
        $password = Hash::make('12345678');

        // Admin account
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => $password,
                'status' => 'active',
                'city' => 'New York',
                'joining_date' => now()->subYear(),
            ],
        );
        $admin->syncRoles('Admin');

        // Dummy name & city data pools
        $firstNames = ['Liam', 'Olivia', 'Noah', 'Emma', 'Oliver', 'Charlotte', 'James', 'Amelia', 'Elijah', 'Sophia', 'William', 'Ava', 'Henry', 'Isabella', 'Lucas', 'Mia', 'Benjamin', 'Evelyn', 'Theodore', 'Harper', 'Mateo', 'Camila', 'Levi', 'Gianna', 'Sebastian', 'Abigail', 'Daniel', 'Luna', 'Jack', 'Ella', 'Alexander', 'Elizabeth', 'Owen', 'Sofia', 'Asher', 'Emily', 'Michael', 'Avery', 'Ethan', 'Mila', 'Leo', 'Scarlett', 'Jackson', 'Eleanor', 'Mason', 'Madison', 'Ezra', 'Layla', 'John', 'Penelope'];

        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'];

        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'Columbus', 'Charlotte', 'Indianapolis', 'San Francisco', 'Seattle', 'Denver', 'Washington', 'Boston'];

        // Create 125 dummy employees
        for ($i = 1; $i <= 125; $i++) {
            $first = $firstNames[($i - 1) % count($firstNames)];
            $last = $lastNames[($i * 3) % count($lastNames)];
            $email = strtolower($first) . '.' . strtolower($last) . $i . '@example.com';
            
            // Custom status: ~25% inactive, ~75% active
            $status = ($i % 4 === 0) ? 'inactive' : 'active';
            $city = $cities[$i % count($cities)];
            $joiningDate = now()->subDays(($i * 7) % 1000)->format('Y-m-d');

            $employee = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "{$first} {$last}",
                    'first_name' => $first,
                    'last_name' => $last,
                    'password' => $password,
                    'status' => $status,
                    'city' => $city,
                    'joining_date' => $joiningDate,
                ],
            );
            $employee->syncRoles('Employee');
        }
    }
}

