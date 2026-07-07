<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $business_name = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'         => ['required', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
            'password'      => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the account with 30 day free trial
        $account = Account::create([
            'name'                 => $validated['business_name'],
            'phone'                => $validated['phone'],
            'email'                => $validated['email'],
            'plan'                 => 'explore',
            'billing_cycle'        => 'monthly',
            'unit_limit'           => 3,
            'trial_ends_at'        => now()->addDays(7),
            'plan_expires_at'      => now()->addDays(7),
            'sms_credits'          => 10,
            'sms_credits_monthly'  => 10,
        ]);

        // Create the user linked to the account
        $user = User::create([
            'account_id' => $account->id,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'role'       => 'owner',
            'password'   => Hash::make($validated['password']),
        ]);

        // Create welcome notification
        \App\Models\Notification::create([
            'account_id' => $account->id,
            'type'       => 'welcome',
            'title'      => 'Welcome to Nyumba!',
            'body'       => 'Your 30 day free trial has started. You have 50 free SMS credits to get you going. Add your first property to get started.',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">

        <!-- Business Name -->
        <div>
            <x-input-label for="business_name" :value="__('Business or property name')" />
            <x-text-input
                wire:model="business_name"
                id="business_name"
                class="block mt-1 w-full"
                type="text"
                name="business_name"
                required
                placeholder="e.g. Mwangi Properties" />
            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
        </div>

        <!-- Full Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Your full name')" />
            <x-text-input
                wire:model="name"
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                required
                autofocus
                autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone number')" />
            <x-text-input
                wire:model="phone"
                id="phone"
                class="block mt-1 w-full"
                type="text"
                name="phone"
                required
                placeholder="07XX or 01XX" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                wire:model="password"
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}"
                wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Create account') }}
            </x-primary-button>
        </div>
    </form>
</div>