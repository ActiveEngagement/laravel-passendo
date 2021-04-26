# Passendo


![PHP Composer](https://github.com/actengage/laravel-message-gears/workflows/PHP%20Composer/badge.svg)

This is a Laravel package that makes tracking Passendo clicks easy. This package includes migrations, models, jobs, and the various handles to retry failed attempts.

    composer require actengage/laravel-passendo

## Basic Usage

The easiest way to use this package is to attach the trait to an Eloquent model.
You must implement a `cpa()` and `tid()` method to fetch the CPA's and tracking
ID's (respectively).

``` php
use Actengage\LaravelPassendo\Contracts\TrackPassendoClicks as TrackPassendoClicksInterface;
use Actengage\LaravelPassendo\TrackPassendoClicks;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements TrackPassendoClicksInterface {

    use Optionable, TrackPassendoClicks;

    public function cpa(): int
    {
        return $this->options->get('cpa');
    }

    public function tid(): string
    {
        return $this->tracking_id;
    }
}
```

## Use the `Click` model.

You can also create `\Actengage\LaravelPassendo\Click` models just as any other
Eloquent model. Give the `tracking_id` and `cpa` attributes a value, and the 
jobs will automatically dispatch.

``` php
use Actengage\LaravelPassendo\Click;

// Manually create a click model
$click = Click::create([
    'tracking_id' => 'test1',
    'cpa' => 1
]);
```

## Polymorphic Relationships.

You may optionally associate a polymorphic relationship, which associates the
click to a parent model.

``` php
$user = User::firstOrCreate([
    'email' => 'test@test.com'
]);

$click->parent()->associate($user);
```

## Using the `TrackPassendoClicks` trait.

If you are implementing the `\Actengage\LaravelPassendo\TrackPassendoClicks`
trait, then you can use the `clicks()` helper to create and associate a model
at once.

``` php
User::create()->clicks()->create([
    'tracking_id' => 'test2',
    'cpa' => 1
])