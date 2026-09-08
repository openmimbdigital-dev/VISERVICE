<?php

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;

/**
 * Guías que vienen con el sistema. Se identifican por slug, de modo que al
 * volver a sembrar se actualiza el contenido sin duplicar la guía ni perder
 * los enlaces que ya se hayan compartido.
 */
class GuidesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->guides() as $guide) {
            Guide::query()->updateOrCreate(
                ['slug' => $guide['slug']],
                $guide
            );
        }

        $this->command?->info(count($this->guides()).' guía(s) sincronizada(s).');
    }

    /** @return list<array<string, mixed>> */
    private function guides(): array
    {
        return [
            [
                'slug'       => 'habilitar-negocio-facturacion-electronica-dian',
                'title'      => 'Habilitar un negocio para facturar ante la DIAN',
                'module'     => 'Facturación electrónica',
                'type'       => Guide::TYPE_ONBOARDING,
                'summary'    => 'Qué debe traer el negocio del portal de la DIAN y qué se configura en el sistema para poder emitir.',
                'sort_order' => 10,
                'published'  => true,
                'content'    => $this->dianOnboarding(),
            ],
        ];
    }

    private function dianOnboarding(): string
    {
        return <<<'MARKDOWN'
        Facturar electrónicamente no se activa solo con un botón: la DIAN exige que
        cada negocio esté habilitado a su nombre y que designe a un proveedor
        tecnológico. Esta guía cubre las tres partes del proceso y en qué orden van.

        > El negocio hace unos trámites que nosotros no podemos automatizar, porque
        > la DIAN no expone un servicio para consultarlos. Conviene decírselo desde
        > el principio para que no espere que todo salga de la aplicación.

        ## Antes de empezar: qué depende de quién

        | Paso | Quién lo hace | Dónde |
        |---|---|---|
        | Habilitarse como facturador electrónico | El negocio | Portal DIAN |
        | Designar a TITANIO (Delcop) como proveedor | El negocio | Portal DIAN |
        | Obtener resolución y clave técnica | El negocio | Portal DIAN |
        | Cargar los datos y registrar el perfil | Nosotros o el negocio | Este sistema |
        | Configurar el ambiente de producción | Delcop | Por correo |

        ## 1. El negocio se habilita ante la DIAN

        Entra a **https://catalogo-vpfe-hab.dian.gov.co/** con la cuenta DIAN del
        NIT. La pestaña depende del contribuyente: **Empresa** para persona
        jurídica, **Persona** para persona natural.

        Ahí completa el set de pruebas que la DIAN exige. Es un trámite del negocio
        y puede tomar días. Sin esto, nada de lo demás sirve.

        ## 2. El negocio designa a TITANIO como proveedor

        En el mismo portal:

        1. **Perfil → Configuración**
        2. **Registro y Habilitación → Documentos Electrónicos**
        3. **Factura Electrónica**
        4. **Configurar Modos de Operación** y elige al proveedor tecnológico
        5. **Detalles de Set de Pruebas**

        Si el negocio no designa al proveedor, la DIAN rechaza los documentos que
        enviemos a su nombre aunque todo lo demás esté bien.

        ## 3. Qué datos debe copiar del portal

        En **Detalles de Set de Pruebas** aparecen todos juntos. Hay que pedirle
        estos seis:

        - **Número de resolución**
        - **Prefijo** (en pruebas suele ser `SETT`)
        - **Rango autorizado**, desde y hasta
        - **Fechas de vigencia**, desde y hasta
        - **Clave técnica** — una cadena larga, distinta para cada NIT
        - **Identificación y PIN del software**

        La clave técnica es lo más delicado: con ella se calcula el CUFE de cada
        factura. Si está mal, la DIAN rechaza todo con `FAD06 — Valor del CUFE no
        está calculado correctamente`, y el mensaje no dice cuál es el dato malo.

        ## 4. Configurar el negocio en el sistema

        En **Facturación electrónica** se crea la configuración del negocio y se
        cargan los datos del paso anterior. La pantalla avisa qué falta antes de
        poder emitir; revisa que no quede ningún aviso pendiente.

        Además de la resolución, el negocio necesita tener bien sus propios datos,
        porque la DIAN los compara contra el RUT:

        - **NIT con su dígito de verificación correcto**
        - **Razón social exactamente como figura en el RUT.** Para persona natural
          van los apellidos primero: `HURTADO YENERIS ALEX DAVID`, no
          `ALEX DAVID HURTADO YENERIS`. Si no coincide aparece la notificación
          `FAJ43b`
        - **Dirección, ciudad y correo.** La ciudad debe tener código DANE
        - **Responsabilidades fiscales** según el RUT. Un negocio marcado como
          *No responsable de IVA* no debe facturar IVA

        ## 5. Registrar el perfil ante el proveedor

        Con la resolución cargada, el botón **«Registrar ante el proveedor»** crea
        la empresa emisora en la plataforma de TITANIO y guarda el identificador de
        perfil (`tr_tipo_id`) que devuelve. Es el único paso automático.

        > Este botón todavía no se ha usado con un cliente real: a nosotros Delcop
        > nos creó la empresa a mano. Conviene estrenarlo con un NIT de prueba
        > antes de ofrecérselo a un negocio.

        ## 6. Probar antes de pasar a producción

        Emite una factura desde una orden de trabajo y revisa el estado. El
        documento pasa por cuatro etapas y solo la última importa:

        ```
        Transacción recibida → Transacción Validada → Enviado a la DIAN → ...
        ```

        Que el proveedor devuelva CUFE y QR **no significa que la DIAN la aceptó**:
        eso es solo su validación interna. El veredicto llega al consultar el
        estado del documento.

        En los mensajes de la DIAN, cada regla viene marcada como **Rechazo** o
        **Notificación**. Solo las de Rechazo tumban el documento; las
        notificaciones conviene atenderlas, pero no bloquean.

        Si el rechazo es por CUFE, `php artisan dian:verify-cufe` recalcula el
        código con la clave configurada y lo compara con el que envió el proveedor.
        Sirve para saber si el problema son nuestros datos o los de ellos.

        ## 7. Pasar a producción

        Cuando el negocio obtenga su resolución de producción ante la DIAN, recibe
        un PDF de autorización. **Ese PDF hay que enviárselo a Delcop** para que
        configuren el perfil productivo: no hay forma de hacerlo desde el sistema.

        Después se cambia el entorno de la configuración a *Producción* y se cargan
        el número de resolución, el rango y las fechas nuevas. El consecutivo
        arranca donde diga la resolución de producción.

        ## Errores frecuentes

        | Mensaje | Qué significa |
        |---|---|
        | `FAD06` | El CUFE no cuadra. Casi siempre es la clave técnica o el ambiente del perfil |
        | `FAV06` | El total de una línea no es igual a cantidad × precio |
        | `FAJ43b` | El nombre enviado no coincide con el del RUT |
        | `FAB07b` / `FAB08b` | Las fechas de vigencia no son las de la resolución registrada |
        | `Regla 90` | Ese consecutivo ya se envió antes; hay que avanzar al siguiente |
        | `RUT01` | Aviso informativo de la DIAN. No bloquea |

        La DIAN entrega **un solo rango de pruebas por NIT**. Los consecutivos que
        se gastan no se recuperan, así que conviene no reiniciarlos al refrescar
        datos de prueba.
        MARKDOWN;
    }
}
