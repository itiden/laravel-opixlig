# Laravel Image

This is a work in progress.

## Example

```php
<x-image::responsive
    src="{{ $image->url }}"
    placeholder="blur"
    sizes="100vw"
    alt="{{ $image->alt }}"
    width="{{ $image->width() }}"
    height="{{ $image->height() }}"
    class="w-full object-cover max-h-[1000px]"
/>
```
