# [Custom Post Types](https://github.com/moxie-lean/wp-cpt)

> This library will allow you to create more easily custom post types for wordpress without too much effort.

## Installation

The easiest way to install this package is by using composer from your
terminal:

```bash
composer require moxie-lean/wp-cpt
```

Or by adding the following lines on your `composer.json` file

```json
"require": {
  "moxie-lean/wp-cpt": "dev-master"
}
```

This will download the file from the [packagist site](https://packagist.org/packages/moxie-lean/wp-cpt) 
and the latest version located on master branch of the repository. 

After that you can need to include the `autoload.php` file in order to
be able to autoload the class during the object creation.

```php
include '/vendor/autoload.php';
```

Then you only need to create a new `Cpt` object with the required params
for your custom post type, if you are using the library in a theme you
might need to add the function in the `init` action as an example: 

```php
add_action( 'init', function() {
    $testimonials = new \Lean\Cpt([
        'singular' => 'Testimonial',
        'plural' => 'Testimonials',
        'post_type' => 'testimonials',
        'slug' => 'testimonial',
    ]);
    $testimonials->init();
});
```

The example above allow you to create a new testimonial post type on
your theme.
