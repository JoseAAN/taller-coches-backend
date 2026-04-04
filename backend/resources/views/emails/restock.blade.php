<x-mail::message>
# ¡Buenas noticias!

El producto **{{ $productName }}** que estabas esperando vuelve a estar en stock.

Puedes comprarlo ahora mismo antes de que se vuelva a agotar haciendo clic en el siguiente botón:

<x-mail::button :url="env('VITE_APP_URL', 'http://localhost:5173') . '/products'">
Ir a la Tienda
</x-mail::button>

Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
</x-mail::message>
