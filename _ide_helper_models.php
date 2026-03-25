<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $meal_slot_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CreditTransaction|null $creditTransaction
 * @property-read \App\Models\MealSlot $mealSlot
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking confirmed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking noShow()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereMealSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUserId($value)
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int $amount
 * @property int $balance_after
 * @property string|null $notes
 * @property int|null $booking_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction whereUserId($value)
 */
	class CreditTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property int $reserved_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $can_book
 * @property-read int $total_credits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereReservedBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditWallet whereUserId($value)
 */
	class CreditWallet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $credit_amount
 * @property numeric $price
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereCreditAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealPlan whereUpdatedAt($value)
 */
	class MealPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $meal_slot_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MealSlot $mealSlot
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereMealSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealRating whereUserId($value)
 */
	class MealRating extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $meal_type
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon $booking_cutoff
 * @property \Illuminate\Support\Carbon $serving_start
 * @property \Illuminate\Support\Carbon $serving_end
 * @property int $max_capacity
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read int $confirmed_count
 * @property-read bool $is_cutoff_passed
 * @property-read int $pending_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Menu> $menu
 * @property-read int|null $menu_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MealRating> $ratings
 * @property-read int|null $ratings_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot today()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereBookingCutoff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereMaxCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereMealType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereServingEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereServingStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MealSlot whereUpdatedAt($value)
 */
	class MealSlot extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $meal_slot_id
 * @property string $dish_name
 * @property string|null $description
 * @property bool $is_vegetarian
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MealSlot $mealSlot
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu vegetarian()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereDishName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIsVegetarian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereMealSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin \Laravel\Sanctum\HasApiTokens
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'], \DateTimeInterface|\DateInterval|int|null $expiresAt = null)
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $room_number
 * @property string|null $phone
 * @property string|null $qr_token
 * @property string|null $fcm_token
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditTransaction> $creditTransactions
 * @property-read int|null $credit_transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MealRating> $mealRatings
 * @property-read int|null $meal_ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $pendingBookings
 * @property-read int|null $pending_bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\CreditWallet|null $wallet
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFcmToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereQrToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

