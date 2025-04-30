# Opixlig

Perfectly sized. Never pixelated.

**Opixlig** is a Laravel-friendly image component inspired by modern frameworks like Next.js — designed to make responsive, optimized images effortless. It automatically generates and serves the right image size and format for every device, keeping your pages fast, sharp, and beautifully adaptive. Write clean Blade, and let **Opixlig** handle the rest.

`composer require itiden/opixlig`

## Example

```php
<x-opixlig::responsive
    src="{{ $image->url }}"
    placeholder="blur"
    sizes="100vw"
    alt="{{ $image->alt }}"
    width="{{ $image->width() }}"
    height="{{ $image->height() }}"
    class="w-full object-cover max-h-[1000px]"
/>
```
