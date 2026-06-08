<?php

namespace Database\Seeders;

use App\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['nosotros', 'Nosotros', "Somos Ordasi, un marketplace que conecta tiendas y compradores de todo el país. Nuestra misión es que vender y comprar online sea simple, seguro y cercano."],
            ['como-comprar', 'Cómo comprar', "1) Buscá el producto que querés.\n2) Agregalo al carrito y elegí variantes si corresponde.\n3) Finalizá la compra eligiendo dirección y forma de entrega.\n4) Coordiná el pago con el vendedor o pagá online cuando esté disponible."],
            ['como-vender', 'Cómo vender', "Convertite en vendedor desde «Vendé acá». Completá los datos de tu tienda, esperá la aprobación y empezá a publicar tus productos. Gestioná ventas, preguntas y envíos desde tu panel."],
            ['medios-de-pago', 'Medios de pago', "Aceptamos transferencia bancaria y, próximamente, tarjetas de crédito y débito a través de MercadoPago. Coordiná con cada tienda la forma de pago disponible."],
            ['terminos', 'Términos y condiciones', "Al usar Ordasi aceptás nuestros términos de uso. Los productos publicados son responsabilidad de cada vendedor. Ordasi actúa como intermediario entre las partes."],
            ['privacidad', 'Política de privacidad', "Cuidamos tus datos. Solo usamos tu información para procesar pedidos y mejorar tu experiencia. No compartimos tus datos con terceros sin tu consentimiento."],
        ];

        foreach ($pages as $i => $p) {
            Page::updateOrCreate(
                ['slug' => $p[0]],
                ['title' => $p[1], 'content' => $p[2], 'is_published' => true, 'order_column' => $i]
            );
        }
    }
}
