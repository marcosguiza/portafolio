# Revisión de Contenido — Showroom Completo
> Base en español para limpieza, estandarización y posterior traducción a EN / PT
> Generado: 2026-11-10

---

## NOTAS GENERALES ANTES DE REVISAR

### Problemas detectados que requieren atención:

1. **Heroes en inglés en VIACHAIN** — Varios productos VIACHAIN tienen el campo `hero` igual en los 3 idiomas (ES/EN/PT), con texto en inglés. Deben traducirse al español.
2. **SC 1000 duplicado** — Aparece como producto en SKYWAVE (con contexto satelital industrial) y también en VIACHAIN (con contexto de transporte). Tienen datos ligeramente distintos (`env`, `gnss`, `certs`). Revisar cuál es la versión correcta o si deben diferenciarse.
3. **TS 310, DS 300, DS 310** — Aparecen en accesorios de SKYWAVE y también en VIACHAIN. Las descripciones son distintas. Evaluar si unificar o mantener diferenciadas por plataforma.
4. **GT 1200 / GT 1210 / GT 1220 / GT 1230** — Los 4 comparten la misma imagen (`GT12XX-VIACHAIN.png`). Verificar si hay imágenes individuales disponibles.
5. **PT 6100 dimensiones** — Están en pulgadas (`7.5 × 4.3 × 1.5 in`), no en mm. Mismo para PT 7000. ¿Estandarizar a mm?
6. **CT 1000 `memory`** — Valor `>2 000 mensajes · 90+ días` es de tipo mensaje, no memoria técnica. Revisar si aplica como campo.
7. **DS 100** — Tiene `compatible: ["GT 1100"]` pero el GT 1100 no está en el showroom. ¿Es un producto legacy?
8. **Euroscan Back/Side/Temp** — Están referenciados como accesorios de Euroscan MX2/X3 y GT 1100 (no listados en showroom). ¿Siguen vigentes?
9. **ST 4300** — Producto marcado como "Nuevo". Verificar que todos los datos sean correctos y definitivos.
10. **ST 6000** campo `prog` — Es un string simple (`"Lua · SDK · SOTA"`) en lugar de objeto trilingüe. Mismo ST 6100 y ST 9100. Inconsistencia menor.
11. **ST 2100** — `dims` y `memory` están vacíos. Completar si existe información.
12. **ST 4000** — `dims` y `memory` están vacíos. Completar si existe información.

---

## ESTRUCTURA DE CADA FICHA

Cada producto sigue este orden estandarizado:

```
Nombre | Categoría | Role (1 línea)
Hero (frase comercial cursiva)
Descripción (párrafo completo)
Especificaciones técnicas:
  - Satelital
  - Celular
  - GNSS
  - Energía/Alimentación
  - Ambiente / IP / Temperatura
  - Dimensiones
  - Memoria
  - Programación/Configuración
  - Respaldo de batería: Sí/No
  - Certificaciones
I/O (lista de entradas/salidas)
Puertos (lista)
Conector 16-pin (si aplica)
Hotspots (puntos de interés en imagen)
```

---

# MARCA: SKYWAVE
## Portafolio ST & SC — Módems y Terminales

---

## PRODUCTO 01 · ST 2100

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st2100` |
| **Nombre** | ST 2100 |
| **Categoría** | Módem satelital robusto |
| **Role** | Módem sellado para integración en controladores externos. |
| **Hero** | El módem satelital base de la familia ST. |
| **Descripción** | Módem bidireccional IDP/OGx pensado para OEMs que embeben su aplicación en un controlador externo. Entrega conectividad satelital robusta con carcasa sellada IP67. |
| **Satelital** | IDP/OGx |
| **Celular** | — |
| **GNSS** | GPS · GLONASS · Galileo · BeiDou |
| **Energía** | 9–32 V |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Dimensiones** | ⚠️ VACÍO — completar |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | App externa |
| **Respaldo** | No |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- Reset out/in
- Event notifications
- 1 PPS
- Accelerometer

**Puertos:**
- RS232

**Hotspots:**
1. `(50%, 40%)` → Antena satelital integrada → apunta a `satellite`
2. `(72%, 60%)` → Interfaz RS232 al controlador → apunta a `ports`

**Documentos:**
- Datasheet: ST2100_SPA.pdf / ST2100_ENG.pdf / ST2100_PTB.pdf
- Hardware Guide: T412 ST 21xx Hardware Guide.pdf
- Installation Guide: T411 ST 21xx Installation Guide.pdf

---

## PRODUCTO 02 · ST 4000

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st4000` |
| **Nombre** | ST 4000 |
| **Categoría** | Módulo dual embebible |
| **Role** | Módulo OEM dual satelital + LTE-M para alta integración. |
| **Hero** | Módulo dual satelital + celular para embebido OEM. |
| **Descripción** | Módulo en formato placa que combina IDP/OGx con LTE-M para integradores que necesitan reducir tamaño y costo en soluciones híbridas de alto volumen. |
| **Satelital** | IDP/OGx |
| **Celular** | LTE-M |
| **GNSS** | GPS · GLONASS · Galileo · BeiDou |
| **Energía** | 3.8 VDC |
| **Ambiente** | −40 °C / +85 °C |
| **Dimensiones** | ⚠️ VACÍO — completar |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | OEM / embebido |
| **Respaldo** | No |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- 4× IO (DIO/AIN) 3V TTL
- 1 PPS
- Accelerometer

**Puertos:**
- UART
- I²C

**Hotspots:**
1. `(30%, 40%)` → Sección satelital IDP/OGx → apunta a `satellite`
2. `(70%, 50%)` → Módulo LTE-M integrado → apunta a `cellular`
3. `(50%, 75%)` → Conexión al host vía UART / I²C → apunta a `ports`

**Documentos:**
- Datasheet: ST4000_SPA.pdf / ST4000_ENG.pdf / ST4000_PTB.pdf
- ⚠️ Sin Hardware Guide ni Installation Guide definidos

---

## PRODUCTO 03 · ST 4300 ✦ NUEVO

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st4300` |
| **Nombre** | ST 4300 |
| **Categoría** | Módulo dual embebible · Nuevo |
| **Role** | Módulo módem integrado híbrido satelital-celular. |
| **Hero** | Conectividad IoT global integrada, diseñada para acelerar la integración. |
| **Descripción** | La Serie ST 4300 facilita agregar conectividad híbrida satelital-celular a productos IoT industriales en formato de placa hija, simplificando la integración y acelerando el tiempo de salida al mercado. Ideal para OEMs que necesitan la capacidad del ST 4000 sin diseñar directamente alrededor del módulo de montaje superficial. |
| **Satelital** | OGx |
| **Celular** | LTE Cat M1 |
| **GNSS** | GPS/QZSS · GLONASS · BeiDou · Galileo |
| **Energía** | 3.8 V (idle 200 µA) |
| **Ambiente** | −40 °C / +85 °C |
| **Dimensiones** | 72.3 × 37.1 × 7.4 mm · 19 g |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | OEM / AT commands |
| **Respaldo** | No |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- GPIO6 (eventos satelitales)
- SIM dual (embebida + Nano)

**Puertos:**
- UART AT commands
- Antenas U.FL (cel · sat/GNSS)

**Hotspots:**
1. `(30%, 40%)` → Módulo OGx satelital → apunta a `satellite`
2. `(70%, 45%)` → Módulo LTE Cat M1 multi-banda → apunta a `cellular`
3. `(50%, 75%)` → GNSS u-blox M10 cuatro constelaciones → apunta a `gnss`

**Documentos:**
- ⚠️ Sin datasheets ni manuales definidos aún

---

## PRODUCTO 04 · ST 6000

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st6000` |
| **Nombre** | ST 6000 |
| **Categoría** | Terminal programable OEM |
| **Role** | Terminal programable sin carcasa para integración OEM directa. |
| **Hero** | Toda la potencia del ST 6100, sin carcasa. |
| **Descripción** | Plataforma programable Lua que entrega la funcionalidad completa del ST 6100 en formato OEM, lista para integrarse en el ensamblaje final del fabricante. |
| **Satelital** | IDP/OGx |
| **Celular** | — |
| **GNSS** | GPS · GLONASS · BeiDou |
| **Energía** | 9–32 V |
| **Ambiente** | −40 °C / +85 °C |
| **Dimensiones** | ⚠️ VACÍO — completar |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Lua · SDK · SOTA |
| **Respaldo** | No |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- 4× GPIO (DIO/AIN)
- 2× DOUT
- 1 PPS
- Accelerometer

**Puertos:**
- RS-232
- RS-485

**Hotspots:**
1. `(32%, 45%)` → Módulo satelital IDP/OGx → apunta a `satellite`
2. `(68%, 45%)` → Plano de tierra RF blindado → (sin specKey)

**Documentos:**
- Datasheet: ST6000_SPA.pdf / ST6000_ENG.pdf / ST6000_PTB.pdf
- Hardware Guide: T407 ST 60xx Hardware Guide.pdf
- ⚠️ Sin Installation Guide definido

---

## PRODUCTO 05 · SC 1000 ⚠️ DUPLICADO VIACHAIN

| Campo | Valor actual (ES) — versión SKYWAVE |
|-------|--------------------------------------|
| **ID** | `sc1000` |
| **Nombre** | SC 1000 |
| **Categoría** | Rastreador solar satelital |
| **Role** | Rastreador solar de bajo costo para activos que antes no se podían rastrear. |
| **Hero** | Visibilidad inteligente alimentada por el sol. |
| **Descripción** | Terminal configurable, alimentado por panel solar y batería recargable. Instalación sin cables, soporte para sensores BLE. Diseñado para OGx. |
| **Satelital** | OGx |
| **Celular** | — |
| **GNSS** | GPS · GLONASS · Galileo · BeiDou |
| **Energía** | Panel solar + batería Li-ion |
| **Ambiente** | IP67 · IPX9K · −35 °C / +75 °C · ATEX Z2 · CID2 |
| **Dimensiones** | 224 × 76 × 28 mm |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable |
| **Respaldo** | Sí (batería) |
| **Certificaciones** | ⚠️ No definidas (en VIACHAIN sí: FCC/IC · CE RED · ATEX Zone 2 · CID2 · Inmarsat · ROHS · WEEE) |

> ⚠️ **Diferencias vs versión VIACHAIN del mismo producto:**
> - Temp. mínima: −35°C (SKYWAVE) vs −30°C (VIACHAIN) — ¿cuál es correcta?
> - Certificaciones: solo en VIACHAIN tienen lista completa
> - GNSS SKYWAVE incluye Galileo, VIACHAIN solo GPS/GLONASS/BeiDou/Galileo (igual)
> - `env` SKYWAVE: ATEX Z2 · CID2 — VIACHAIN: ATEX Zone 2 (sin CID2 en env, pero sí en certs como CID2)

**I/O:**
- 3-axis accelerometer

**Puertos:**
- BLE 5.2

**Hotspots:**
1. `(46%, 28%)` → Panel solar integrado → apunta a `power`
2. `(70%, 45%)` → Antena satelital OGx → apunta a `satellite`
3. `(20%, 70%)` → Montaje directo sin cables → (sin specKey)

**Documentos:**
- Datasheet: SC1000_SPA.pdf / SC1000_ENG.pdf / SC1000_PTB.pdf
- Hardware Guide: GJD131 SC 1000 Hardware Guide (for Partners).pdf
- Installation Guide: GJD119 SC 1000 Installation Guide (for Partners).pdf

---

## PRODUCTO 06 · ST 6100

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st6100` |
| **Nombre** | ST 6100 |
| **Categoría** | Terminal programable esencial |
| **Role** | Terminal satelital bidireccional para seguimiento, monitoreo y control. |
| **Hero** | La próxima generación de terminales satelitales. |
| **Descripción** | Terminal totalmente programable con motor Lua, SDK y Terminal Apps. Antena interna de bajo ángulo de elevación para aplicaciones terrestres y marítimas. |
| **Satelital** | IDP/OGx |
| **Celular** | — |
| **GNSS** | GPS · GLONASS · BeiDou |
| **Energía** | 9–32 V |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Dimensiones** | 126 × 126 × 49 mm |
| **Memoria** | RAM 4MB · NVM 6MB |
| **Programación** | Lua · SDK · SOTA |
| **Respaldo** | No |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- 4× I/O analog/digital
- 3-axis accelerometer

**Puertos:**
- RS-232
- RS-485

**Hotspots:**
1. `(48%, 35%)` → Antena satelital de bajo ángulo de elevación → apunta a `satellite`
2. `(74%, 62%)` → Carcasa sellada IP67 → apunta a `env`
3. `(25%, 55%)` → Conector lateral o inferior → apunta a `ports`

**Documentos:**
- Datasheet: ST6100_SPA.pdf / ST6100_ENG.pdf / ST6100_PTB.pdf
- Hardware Guide: T406 ST 61xx Hardware Guide.pdf
- Installation Guide: R9015 ST 61xx Installation Guide.pdf

---

## PRODUCTO 07 · ST 8100 ✦ NUEVO

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st8100` |
| **Nombre** | ST 8100 |
| **Categoría** | Terminal dual solar · Nuevo |
| **Role** | Terminal de modo dual satelital + celular con energía solar. |
| **Hero** | Programable, solar, dual. Donde no hay energía externa. |
| **Descripción** | Ideal para activos sin alimentación externa. Enruta por celular cuando está disponible y por satélite cuando no. Soporta sensores BLE integrados; la variante Advanced añade interfaz cableada de 16-pin. |
| **Satelital** | IDP · OGx |
| **Celular** | LTE-M · NB-IoT · 2G |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo |
| **Energía** | Solar 0.86 W + batería Li-ion 1100 mAh |
| **Ambiente** | IP67 · IPX9K · −35 °C / +75 °C |
| **Dimensiones** | 285 × 93 × 35 mm |
| **Memoria** | PSRAM 8MB · NVM 16MB |
| **Programación** | Lua · SDK · SOTA/FOTA |
| **Respaldo** | Sí (batería) |
| **Certificaciones** | ⚠️ No definidas |

**I/O (base):**
- Acelerómetro 3 ejes

**Puertos (base):**
- BLE 5.0

**Conector 16-pin (variante Advanced):**

*I/O adicional:*
- 2× IO config.
- 2× AIN voltaje (0-15V)
- 2× AIN 4-20 mA
- 2× salida 24V / 30mA

*Puertos adicionales:*
- RS-232
- RS-485/J1708
- 1-Wire

**Hotspots:**
1. `(48%, 30%)` → Panel solar 0.86 W integrado → apunta a `power`
2. `(70%, 55%)` → Conector 16-pin variante Advanced → apunta a `io_16pin`
3. `(25%, 60%)` → BLE 5.0 para sensores y app móvil → apunta a `ports`

**Documentos:**
- Datasheet: ST8100_SPA.pdf / ST8100_ENG.pdf / ST8100_PTB.pdf
- Hardware Guide: T416 ST 8100 Hardware Guide.pdf
- Installation Guide: T417 ST 8100 Installation Guide.pdf

---

## PRODUCTO 08 · ST 9100

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st9100` |
| **Nombre** | ST 9100 |
| **Categoría** | Terminal dual multi-interfaz |
| **Role** | Terminal programable con 2 buses CAN, 3 SIMs y respaldo 48h. |
| **Hero** | El terminal más versátil del portafolio. |
| **Descripción** | Terminal dual satelital + 4G LTE con fallback 3G/2G. 2 buses CAN, 3 SIMs, batería de respaldo >48 h. |
| **Satelital** | IDP/OGx |
| **Celular** | 4G LTE · 3G · 2G fallback |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo |
| **Energía** | 9–32 V |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Dimensiones** | 181 × 113 × 47 mm |
| **Memoria** | ⚠️ Li-ion 2000 mAh — esto es batería, no memoria. ¿Renombrar campo? |
| **Programación** | Lua · SDK · SOTA |
| **Respaldo** | Sí (>48h) |
| **Certificaciones** | ⚠️ No definidas |

**I/O:**
- 4× I/O
- 2× dedicated out
- 4× A/D in
- 2× AIN 4-20 mA

**Puertos:**
- 2× RS-232
- RS-485/J1708
- 2× CAN
- 1-Wire
- BLE 5.0

**Hotspots:**
1. `(40%, 25%)` → LEDs programables de estado → (sin specKey)
2. `(72%, 60%)` → Conector industrial multipin → apunta a `ports`
3. `(20%, 75%)` → Pies de montaje reforzados → (sin specKey)

**Documentos:**
- Datasheet: ST9100_SPA.pdf / ST9100_ENG.pdf / ST9100_PTB.pdf
- Hardware Guide: T413 ST 91xx Hardware Guide.pdf
- Installation Guide: T414 ST 91xx Installation Guide.pdf

---

# ACCESORIOS SKYWAVE

---

## ACC-SK-01 · DB 300

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `db300` |
| **Nombre** | DB 300 |
| **Categoría** | Botón de pánico |
| **Hero** | Alerta de auxilio inmediata vía Bluetooth 6.0 con 10+ años de autonomía. |
| **Descripción** | El DB 300 (Botón de pánico) es un botón de auxilio Bluetooth 6.0 que permite a trabajadores en campo enviar una alerta de emergencia a la terminal Skywave más cercana con un solo toque. Basado en el SoC Nordic nRF54L15, incluye acelerómetro de 3 ejes para detección de caída e impacto. La batería Li-SOCl2 de 10400 mAh (reemplazable) garantiza más de 10 años de operación sin mantenimiento. Bluetooth Channel Sounding permite mediciones de distancia precisas para localización indoor. PA opcional eleva la potencia de transmisión hasta +20 dBm para mayor alcance en exteriores. |
| **Compatible con** | ST 8100 · ST 9100 · SC 1000 |
| **Dimensiones** | 74 × 74 × 23 mm · 125 g |
| **Ambiente** | IP67 · −20 °C / +60 °C |
| **Puertos** | BLE 6.0 |
| **Datasheet** | GJD139 DB 300 Installation Guide_SKYWAVE.pdf |

---

## ACC-SK-02 · TS 310 ⚠️ TAMBIÉN EN VIACHAIN

| Campo | Valor actual (ES) — versión SKYWAVE |
|-------|--------------------------------------|
| **ID** | `ts310` |
| **Nombre** | TS 310 |
| **Categoría** | Sensor de temperatura |
| **Hero** | Monitoreo de temperatura de alta precisión para cadena de frío. |
| **Descripción** | El TS 310 es un sensor de temperatura de alta precisión para monitoreo de cadena de frío en tráilers refrigerados, contenedores y almacenes. Transmite lecturas en tiempo real a la terminal Skywave vía BLE para reportes continuos de temperatura. Compartido con la plataforma VIACHAIN. |
| **Compatible con** | ST 8100 · ST 9100 |
| **Dimensiones** | — |
| **Ambiente** | IP67 |
| **Puertos** | BLE |

> ⚠️ **Versión VIACHAIN del mismo TS 310 tiene datos distintos y más completos:**
> - Dimensiones: 136 × 64 × 21 mm · 120 g
> - Ambiente: IP69k · −40 °C / +85 °C (más completo)
> - Precisión: ±0.5°C
> - Batería: Li-ion 1000 mAh, 5 años mínimo
> - Cumple: EN 12830:2018
> - Compatible: RT 8000, RT 8110, RT 8120, Euroscan MX2, ST 9100 series
> **→ Completar versión SKYWAVE con estos datos**

---

## ACC-SK-03 · DS 300 ⚠️ TAMBIÉN EN VIACHAIN

| Campo | Valor actual (ES) — versión SKYWAVE |
|-------|--------------------------------------|
| **ID** | `ds300` |
| **Nombre** | DS 300 |
| **Categoría** | Sensor de puerta inalámbrico |
| **Hero** | Monitoreo de acceso BLE sin cables para tráilers y contenedores. |
| **Descripción** | El DS 300 es un sensor de puerta inalámbrico BLE que detecta aperturas y cierres en tráilers y contenedores sin necesidad de cableado. Batería Li-ion de 1000 mAh con hasta 5 años de operación continua. Compatible con terminales Skywave ST 9100 y SC 1000. Genera alertas ante aperturas no autorizadas y fuera de geocercas. Compartido con la plataforma VIACHAIN. |
| **Compatible con** | ST 8100 · ST 9100 · SC 1000 |
| **Dimensiones** | 102 × 64 × 15 mm · 97 g |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Puertos** | BLE 4.0 |

---

## ACC-SK-04 · DS 310 ⚠️ TAMBIÉN EN VIACHAIN

| Campo | Valor actual (ES) — versión SKYWAVE |
|-------|--------------------------------------|
| **ID** | `ds310` |
| **Nombre** | DS 310 |
| **Categoría** | Sensor de puerta inalámbrico avanzado |
| **Hero** | Sensor de puerta de nueva generación para condiciones intermodales exigentes. |
| **Descripción** | El DS 310 es el sensor de puerta inalámbrico de nueva generación de ORBCOMM. BLE 5.1 con mayor cobertura y eficiencia energética. Diseño compacto IP67 de bajo perfil, certificado MIL-STD-810G, sin cables ni antenas externas. Compatible con terminales Skywave ST 8100 y ST 9100. |
| **Compatible con** | ST 8100 · ST 9100 |
| **Dimensiones** | 102 × 64 × 15 mm · 97 g |
| **Ambiente** | IP67 · MIL-STD-810G · −40 °C / +85 °C |
| **Puertos** | BLE 5.1 |

> ⚠️ **Versión VIACHAIN agrega SAE J1455:** `IP67 · SAE J1455 · MIL-STD-810G · −40 °C / +85 °C`
> Y compatible con más dispositivos: GT 1200/1210/1220/1230, CT 5300

---

## ACC-SK-05 · Sentrius BT610

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `bt610` |
| **Nombre** | Sentrius BT610 |
| **Categoría** | Sensor BLE industrial |
| **Hero** | Sensor BLE industrial de Laird Connectivity para monitoreo de temperatura y humedad. |
| **Descripción** | El Sentrius BT610 de Laird Connectivity es un sensor BLE 5 industrial certificado para ambientes exigentes. Mide temperatura, humedad y aceleración. Su larga duración de batería y encapsulado IP67 lo hacen ideal para monitoreo de activos en transporte refrigerado, almacenes y entornos industriales. Certificado para instalación con terminales Skywave. |
| **Tercero** | Sí (Laird Connectivity) |
| **Compatible con** | ST 8100 · ST 9100 |
| **Dimensiones** | 86 × 43 × 26 mm |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Puertos** | BLE 5 |

---

## ACC-SK-06 · BTSMP1 Eye Sensor

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `btsmp1` |
| **Nombre** | BTSMP1 Eye Sensor |
| **Categoría** | Sensor BLE multifunción |
| **Hero** | Sensor BLE multifunción para monitoreo ambiental y de activos en campo. |
| **Descripción** | El BTSMP1 Eye Sensor es un sensor BLE compacto y multifunción que mide temperatura, humedad, presión atmosférica y aceleración. Diseñado para monitoreo en tiempo real de condiciones ambientales de carga y activos. Certificado para instalación con terminales Skywave ST y SC, comunicándose vía BLE para reporte continuo. |
| **Tercero** | Sí |
| **Compatible con** | ST 8100 · ST 9100 |
| **Dimensiones** | — |
| **Ambiente** | IP67 |
| **Puertos** | BLE |

---

## ACC-SK-07 · DUT-E S7 BLE

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `dute_s7` |
| **Nombre** | DUT-E S7 BLE |
| **Categoría** | Sensor de nivel de combustible |
| **Hero** | Medición capacitiva de combustible de alta precisión con transmisión BLE. |
| **Descripción** | El DUT-E S7 BLE mide el nivel de combustible en tanques de vehículos y objetos estacionarios con principio capacitivo — sin partes móviles para mayor durabilidad. Inaccuracy ≤ 0.2% (estacionario) / ≤ 1% (en movimiento). Longitud de sonda: 350–1000 mm (ajustable en campo). Transmisión vía BLE a la terminal Skywave para monitoreo continuo de consumo y detección de fugas o drenajes no autorizados. Tensión de alimentación 10–50 V DC. Temperatura de operación: −40 °C / +85 °C. Corrección térmica automática configurable. Sin necesidad de recalibración ante reducciones de longitud de hasta 30%. |
| **Tercero** | Sí |
| **Compatible con** | ST 8100 · ST 9100 |
| **Dimensiones** | Sonda: 350–1000 mm (ajustable en campo) |
| **Ambiente** | −40 °C / +85 °C |
| **Puertos** | BLE · Analog · RS-232 · RS-485 · CAN J1939 |

> ⚠️ **Campo "Inaccuracy"** — En el texto español se usa la palabra en inglés. ¿Reemplazar por "Inexactitud" o "Error máximo"?

---

---

# MARCA: VIACHAIN
## Portafolio CT · GT · PT · SC — Cargo Intelligence

---

## PRODUCTO 01 · CT 1000

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `ct1000` |
| **Nombre** | CT 1000 |
| **Categoría** | Rastreador de contenedor |
| **Role** | Visibilidad solar de bajo costo para contenedores y tráilers. |
| **Hero** | ⚠️ EN INGLÉS: "Simpler tracking. Smarter decisions." — TRADUCIR |
| **Descripción** | El CT 1000 ofrece visibilidad de activos rentable, eficiencia de conducción y seguridad de carga. Gestores de flota pueden mejorar la utilización monitoreando ubicación, estado, arranques y paradas en múltiples tipos de activos. SIM global con conectividad en 565 redes, 180 países. Instalable en 1 minuto. |
| **Satelital** | — |
| **Celular** | 4G LTE · 3G/2G global |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo · QZSS |
| **Energía** | Solar (panel integrado, sin fuente externa) |
| **Ambiente** | IP69K · IP67 · −20 °C / +70 °C |
| **Dimensiones** | 197 × 50.8 × 50.8 mm |
| **Memoria** | >2 000 mensajes · 90+ días |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | FCC/IC · PTCRB · CE RED 2014/53/EU · ATEX · ROHS · WEEE |

**I/O:**
- 3-axis accelerometer
- Ambient temperature sensor

**Puertos:**
- BLE

**Hotspots:**
1. `(48%, 30%)` → SIM global soldable — 565 redes, 180 países → apunta a `cellular`
2. `(72%, 55%)` → Acelerómetro 3 ejes para detección de movimiento → apunta a `io`
3. `(25%, 65%)` → Enclosure IP69K + IP67 — resistente a presión de agua → apunta a `env`

**Documentos:**
- Datasheet: CT1000_VIACHAIN.pdf (en)
- Hardware Guide: GJD115 CT 1000 Installation Guide.pdf
- Installation Guide: GJD115 CT 1000 Installation Guide.pdf

---

## PRODUCTO 02 · CT 1010

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `ct1010` |
| **Nombre** | CT 1010 |
| **Categoría** | Rastreador de contenedor avanzado |
| **Role** | Visibilidad, seguridad y protección para contenedores. |
| **Hero** | ⚠️ EN INGLÉS: "Visibility. Security. Protection." — TRADUCIR |
| **Descripción** | El CT 1010 es un dispositivo inteligente de monitoreo de contenedores que protege la carga, reduce el riesgo de incendio y hace los contenedores más seguros. Su pod interior detecta temperatura ambiente, movimiento, luz y apertura de puertas. ATEX Zona 2 certificado. Compatible con CrewView para gestión en tiempo real a bordo. |
| **Satelital** | — |
| **Celular** | 4G LTE · 3G/2G |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo · QZSS |
| **Energía** | Li-ion + panel solar (recarga solar) |
| **Ambiente** | IP67 · −30 °C / +70 °C · ATEX Zone 2 |
| **Dimensiones** | 213 × 53 × 51 mm |
| **Memoria** | >2 000 mensajes · 90+ días |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | FCC/IC · PTCRB · CE RED 2014/53/EU · ATEX Zone 2 · ROHS |

**I/O:**
- Near-IR ToF door sensor (open/close)
- Far-IR temperature sensor
- Ambient temperature sensor
- Ambient light sensor
- Tamper detection sensor
- 3-axis accelerometer (×2)

**Puertos:**
- BLE
- RFID EPC Gen 2

**Hotspots:**
1. `(48%, 32%)` → Pod interior — sensor de puerta, temperatura, luz y movimiento → apunta a `io`
2. `(74%, 52%)` → Antena celular y GNSS integradas → apunta a `cellular`
3. `(24%, 65%)` → Batería Li-ion con panel solar integrado → apunta a `power`

**Documentos:**
- Datasheet: CT1010_VIACHAIN.pdf (en)
- Hardware Guide: GJD128 CT 1010 Hardware Guide.pdf
- Installation Guide: GJD129 CT 1010 Installation Guide.pdf

---

## PRODUCTO 03 · CT 3600

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `ct3600` |
| **Nombre** | CT 3600 |
| **Categoría** | Control de contenedor reefer |
| **Role** | Gestión y control remoto de contenedores refrigerados. |
| **Hero** | ⚠️ EN INGLÉS: "Remote reefer container management and control." — TRADUCIR |
| **Descripción** | El CT 3600 ofrece monitoreo, visibilidad y control completo de contenedores reefer con tiempo de despliegue reducido. Instalación sin perforación dentro del gabinete reefer. Monitoreo y corrección de temperatura, inspección pre-viaje remota y geofencing avanzado para optimizar tiempos de ciclo. |
| **Satelital** | — |
| **Celular** | 4G LTE CAT1 · 3G/2G |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo · QZSS |
| **Energía** | 15–36 VAC / 9–32 VDC · batería interna 3.6 Ah |
| **Ambiente** | IP54 · −40 °C / +85 °C |
| **Dimensiones** | 172.37 × 81.03 × 25.58 mm |
| **Memoria** | >2 000 mensajes · >5 000 geocercas onboard |
| **Programación** | Configurable remoto |
| **Respaldo** | Sí (hasta 10 días) |
| **Certificaciones** | FCC/IC · PTCRB · CE Red · RCM (Australia) |

**I/O:**
- 3-axis accelerometer
- Reefer control interface (Carrier + Thermo King)

**Puertos:**
- BLE
- Reefer interface connector

**Hotspots:**
1. `(68%, 38%)` → Conectores naranja — interfaz al refrigerador reefer → apunta a `io`
2. `(22%, 52%)` → Antena celular/GPS + BLE cableada al exterior del gabinete → apunta a `cellular`
3. `(45%, 72%)` → Batería interna 3.6 Ah — hasta 10 días sin alimentación → apunta a `power`

**Documentos:**
- Datasheet: CT3600_VIACHAIN.pdf (en)
- Hardware Guide: GJD122 CT 360x Hardware Guide.pdf
- Installation Guide: GJD123 CT 360x Installation Guide.pdf

---

## PRODUCTO 04 · CT 5300

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `ct5300` |
| **Nombre** | CT 5300 |
| **Categoría** | Rastreador con cámara de carga |
| **Role** | Inteligencia avanzada de activos con cámara de carga integrada. |
| **Hero** | ⚠️ EN INGLÉS: "Advanced asset intelligence with integrated cargo camera." — TRADUCIR |
| **Descripción** | El CT 5300 es un dispositivo de inteligencia de activos que combina seguimiento, cámara de carga 1MP con visión 180° e iluminación IR. Verifica el estado de carga y disponibilidad de activos remotamente. Instalación en corrugaciones verticales u horizontales. Solar 2.5W. |
| **Satelital** | — |
| **Celular** | 4G LTE · 3G/2G global |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo |
| **Energía** | Solar (2.5W) · backup Li-ion |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +75 °C |
| **Dimensiones** | 456 × 106 × 31 mm · 1.3 kg |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | FCC · IC · CE |

**I/O:**
- 1MP 180° FoV cargo camera
- 2× IR illumination LEDs
- Ambient temperature sensor
- Tamper detection sensor
- 3-axis accelerometer (×2)
- Wireless door sensor (optional)

**Puertos:**
- BLE

**Hotspots:**
1. `(48%, 42%)` → Panel solar 2.5W — carga rápida incluso con luz indirecta → apunta a `power`
2. `(75%, 58%)` → Cámara 1MP · FoV 180° · LEDs IR para visión nocturna → apunta a `io`
3. `(22%, 62%)` → Instala en corrugaciones — sin perforación → (sin specKey)

**Documentos:**
- Datasheet: CT5300_VIACHAIN.pdf (en)
- Hardware Guide: GJD148 CT 5300 Hardware Guide.pdf
- Installation Guide: GJD145 CT 5300 Installation Guide.pdf

---

## PRODUCTO 05 · GT 1030

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `gt1030` |
| **Nombre** | GT 1030 |
| **Categoría** | Gateway de transporte y maquinaria |
| **Role** | Visibilidad completa de tráilers, chasis, flatbeds y más. |
| **Hero** | ⚠️ EN INGLÉS: "Complete visibility of transportation assets, chassis, flatbeds and more." — TRADUCIR |
| **Descripción** | El GT 1030 es un dispositivo de telemática compacto y resistente diseñado para rastrear y monitorear una amplia gama de activos de transporte. Conectividad LTE global, soporte para sensores cableados e inalámbricos, conector de 7 vías y batería de respaldo de 19 Wh para hasta 10 meses de operación sin fuente externa. |
| **Satelital** | Opcional (ST 2100) |
| **Celular** | 4G LTE global · 3G/2G fallback |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo · SBAS · QZSS |
| **Energía** | 9–32 VDC · batería backup 19 Wh (hasta 10 meses) |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +85 °C |
| **Dimensiones** | 116 × 89 × 39 mm |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable OTA / RS-232 |
| **Respaldo** | Sí (hasta 10 meses) |
| **Certificaciones** | PTCRB · FCC · IC · CE · HERO · Anatel |

**I/O:**
- 3-axis accelerometer
- 2× digital input
- 1× GPIO
- 1× analog input
- 1× 12V power output (sensors)

**Puertos:**
- Chogori 12-pin
- RS-232
- RS-485
- BLE

**Hotspots:**
1. `(48%, 35%)` → Antenas celular y GNSS integradas — instalación discreta → apunta a `gnss`
2. `(72%, 58%)` → Conector Chogori 12 pines — 7 vías de conexión al activo → apunta a `ports`
3. `(25%, 62%)` → Batería 19 Wh — hasta 10 meses sin alimentación externa → apunta a `power`

**Documentos:**
- Datasheet: GT1030_VIACHAIN.pdf (en)
- Installation Guide: GT 1030 Installation Guide.pdf

---

## FAMILIA GT 12xx — Rastreadores Solares de Nueva Generación
> Los 4 modelos comparten la plataforma base. Se diferencian por sensor de carga y soporte de cámara.

| Modelo | Sensor de carga integrado | Cámara cableada (CS 500) | Cámara BLE (CS 511) |
|--------|--------------------------|--------------------------|---------------------|
| GT 1200 | No | No | No |
| GT 1210 | Sí (ultrasónico + IR) | Sí (via 16-pin) | No |
| GT 1220 | No | Sí (via 16-pin) | No |
| GT 1230 | Sí (integrado) | Sí (via 16-pin) | Sí (BLE) |

---

## PRODUCTO 06 · GT 1200

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `gt1200` |
| **Nombre** | GT 1200 |
| **Categoría** | Rastreador solar para tráileres |
| **Role** | Rastreo solar con soporte para sensores cableados. Sin sensor de carga integrado. |
| **Hero** | ⚠️ EN INGLÉS: "Next-generation solar-powered tracking for dry trailers, containers and transport assets." — TRADUCIR |
| **Descripción** | El GT 1200 es el rastreador solar de nueva generación para tráileres secos, contenedores y activos de transporte. Tecnología solar 20× más rápida que la generación anterior. SIM global LTE, antenas BLE duales integradas y soporte para sensores cableados vía conector 16 pines (opcional). Sin sensor de carga integrado: combina con sensores externos CS 500 o DS 300. |
| **Satelital** | Opcional (accesorio) |
| **Celular** | 4G LTE global · 3G/2G fallback |
| **GNSS** | Multi-constelación de alto rendimiento · precisión típica ≤3m |
| **Energía** | Panel solar integrado · batería Li-ion |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +70 °C |
| **Dimensiones** | 381 × 94 × 25.4 mm · Con 16-pin: 414 × 94 × 25.4 mm |
| **Memoria** | 2KB EEPROM · 16MB Flash · 100 msg S&F (4 000+ extendido) |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | PTCRB · FCC · IC · CE · Anatel |

**I/O (base):**
- ±16g accelerometer
- Ambient temperature sensor

**Puertos (base):**
- BLE 5.0 (dual-integrated)

**Conector 16-pin (opcional):**

*I/O adicional:*
- 9–32 VDC power input
- 4× multipurpose I/O
- Wired door sensor
- TPMS alert light
- Temperature probes (6m / 15m / 22m)
- 2× analog input (0–12V)

*Puertos adicionales:*
- RS-232

**Hotspots:**
1. `(35%, 38%)` → Panel solar de última generación — carga en luz indirecta y latitudes altas → apunta a `power`
2. `(68%, 45%)` → Conector 16-pin opcional — RS-232, 4× I/O, 2× analógico, sensor puerta, TPMS → apunta a `io_16pin`
3. `(50%, 70%)` → Instala en corrugaciones verticales u horizontales sin perforación → (sin specKey)

**Documentos:**
- Datasheet: GT1200_VIACHAIN.pdf (en)
- Hardware Guide: GT0023 GT 12xx Hardware Guide.pdf

---

## PRODUCTO 07 · GT 1210

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `gt1210` |
| **Nombre** | GT 1210 |
| **Categoría** | Rastreador solar con sensor de carga |
| **Role** | Todo-en-uno solar con sensor de carga integrado ultrasónico e infrarrojo. |
| **Hero** | ⚠️ EN INGLÉS: "Next-generation, all-in-one solar-powered tracking device with integrated cargo sensor." — TRADUCIR |
| **Descripción** | El GT 1210 combina todas las capacidades del GT 1200 con un sensor de carga integrado de doble tecnología: ultrasónico (15 cm a >16 m) e infrarrojo de corto alcance. Detecta automáticamente la presencia de carga en tráilers sin hardware adicional. Conector 16 pines opcional para sensores cableados adicionales. |
| **Satelital** | Opcional (accesorio) |
| **Celular** | 4G LTE global · 3G/2G fallback |
| **GNSS** | Multi-constelación de alto rendimiento · precisión típica ≤3m |
| **Energía** | Panel solar integrado · batería Li-ion |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +70 °C |
| **Dimensiones** | 381 × 94 × 25.4 mm · Con 16-pin: 414 × 94 × 25.4 mm |
| **Memoria** | 2KB EEPROM · 16MB Flash · 100 msg S&F (4 000+ extendido) |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | PTCRB · FCC · IC · CE · Anatel |

**I/O (base):**
- Ultrasonic cargo sensor (6" – >53')
- Infrared cargo sensor (short range)
- ±16g accelerometer
- Ambient temperature sensor

**Puertos (base):**
- BLE 5.0 (dual-integrated)

**Conector 16-pin (opcional):**

*I/O adicional:*
- 9–32 VDC power input
- 4× multipurpose I/O
- Wired cargo camera (CS 500 · RS-485)
- Wired door sensor
- TPMS alert light
- Temperature probes (6m / 15m / 22m)
- 2× analog input (0–12V)

*Puertos adicionales:*
- RS-232
- RS-485

**Hotspots:**
1. `(35%, 38%)` → Panel solar de última generación → apunta a `power`
2. `(68%, 45%)` → Sensor de carga integrado — ultrasónico (6"–>53') + infrarrojo → apunta a `io`
3. `(50%, 70%)` → Conector 16-pin opcional — 4× I/O, sensor puerta cableado, TPMS, temperatura → apunta a `io_16pin`

**Documentos:**
- Datasheet: GT1210_VIACHAIN.pdf (en)
- Hardware Guide: GT0023 GT 12xx Hardware Guide.pdf

---

## PRODUCTO 08 · GT 1220

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `gt1220` |
| **Nombre** | GT 1220 |
| **Categoría** | Rastreador solar nueva generación |
| **Role** | Nueva plataforma solar con soporte para cámara de carga cableada. Sin sensor de carga integrado. |
| **Hero** | ⚠️ EN INGLÉS: "Solar-powered tracking for dry trailers, containers and other transport assets." — TRADUCIR |
| **Descripción** | El GT 1220 es la nueva generación de la familia GT 12xx (variante GT12 GL LTE4). Optimizado para tráileres secos y contenedores con soporte para sensor de puerta BLE inalámbrico y cámara de carga cableada CS 500 (RS-485, vía conector 16 pines). Sin sensor de carga integrado. Disponible en variante global (GT12 GL LTE4) y Norteamérica (GT12 LTE1 GL). |
| **Satelital** | Opcional |
| **Celular** | 4G LTE global · 3G/2G fallback |
| **GNSS** | Multi-constelación de alto rendimiento · precisión típica ≤3m |
| **Energía** | Panel solar integrado · batería Li-ion |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +70 °C |
| **Dimensiones** | 415.7 × 91.9 × 25.3 mm · Con 16-pin: 432.6 × 91.9 × 25.3 mm |
| **Memoria** | 2KB EEPROM · 16MB Flash · 100 msg S&F (4 000+ extendido) |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | FCC · IC |

> ⚠️ **GT 1220 tiene menos certificaciones** que GT 1200/1210 (sin PTCRB, CE, Anatel). ¿Correcto o pendiente de aprobación?

**I/O (base):**
- Accelerometer
- Ambient temperature sensor
- BLE wireless door sensor support

**Puertos (base):**
- BLE 5.0 (dual-integrated)

**Conector 16-pin (opcional):**

*I/O adicional:*
- 9–32 VDC power input
- Wired cargo camera (CS 500 · RS-485)

*Puertos adicionales:*
- RS-485

**Hotspots:**
1. `(35%, 38%)` → Nueva plataforma GT12 GL LTE4 — panel solar avanzado → apunta a `power`
2. `(68%, 45%)` → Conector 16-pin opcional — RS-485 para cámara CS 500 cableada → apunta a `io_16pin`
3. `(50%, 70%)` → Instala en corrugaciones verticales u horizontales sin perforación → (sin specKey)

**Documentos:**
- Datasheet: GT1220_VIACHAIN.pdf (en)
- Hardware Guide: GT0023 GT 12xx Hardware Guide.pdf

---

## PRODUCTO 09 · GT 1230

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `gt1230` |
| **Nombre** | GT 1230 |
| **Categoría** | Rastreador solar nueva gen. con sensor de carga |
| **Role** | Nueva plataforma todo-en-uno con sensor de carga integrado y cámara BLE. |
| **Hero** | ⚠️ EN INGLÉS: "Advanced technology for ease and reliability in transport asset tracking." — TRADUCIR |
| **Descripción** | El GT 1230 es la evolución todo-en-uno de la nueva plataforma GT 12xx con sensor de carga integrado. Mejora la visibilidad de carga en tráilers sin hardware adicional. Compatible con sensor de puerta BLE inalámbrico y cámara solar BLE CS 511. Incluye sello de detección de manipulación. Variantes: GT12 GL LTE4 (global) y GT12 LTE1 GL (Norteamérica). |
| **Satelital** | Opcional |
| **Celular** | 4G LTE global · 3G/2G fallback |
| **GNSS** | Multi-constelación de alto rendimiento · precisión típica ≤3m |
| **Energía** | Panel solar integrado · batería Li-ion |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +70 °C |
| **Dimensiones** | 415.7 × 91.9 × 25.3 mm · Con 16-pin: 432.6 × 91.9 × 25.3 mm |
| **Memoria** | 2KB EEPROM · 16MB Flash · 100 msg S&F (4 000+ extendido) |
| **Programación** | Configurable OTA |
| **Respaldo** | No |
| **Certificaciones** | FCC · IC |

> ⚠️ Misma observación que GT 1220 sobre certificaciones incompletas.

**I/O (base):**
- Integrated cargo sensor
- Tamper detection seal
- Accelerometer
- Ambient temperature sensor
- BLE wireless door sensor support
- BLE cargo camera support (CS 511)

**Puertos (base):**
- BLE 5.0 (dual-integrated)

**Conector 16-pin (opcional):**

*I/O adicional:*
- 9–32 VDC power input
- Wired cargo camera (CS 500 · RS-485)

*Puertos adicionales:*
- RS-485

**Hotspots:**
1. `(35%, 38%)` → Nueva plataforma GT12 GL LTE4 — panel solar avanzado → apunta a `power`
2. `(68%, 45%)` → Sensor de carga integrado + sello de detección de manipulación → apunta a `io`
3. `(50%, 70%)` → Compatible CS 511 — cámara BLE solar inalámbrica de carga → apunta a `io`

---

## PRODUCTO 10 · PT 6100

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `pt6100` |
| **Nombre** | PT 6100 |
| **Categoría** | Monitor de reefer para tráiler |
| **Role** | Monitoreo y control completo de tráilers refrigerados. |
| **Hero** | ⚠️ EN INGLÉS: "Complete reefer monitoring and control." — TRADUCIR |
| **Descripción** | El PT 6100 ofrece gestión integral de combustible y temperatura, mantenimiento, logística y cumplimiento regulatorio para tráilers refrigerados. Compatible con Carrier Transicold y Thermo King. Soporte para hasta 3 sensores de temperatura. Batería recargable con reporte por 10 días sin alimentación vehicular. |
| **Satelital** | — |
| **Celular** | 4G LTE · 3G · 2G |
| **GNSS** | Opcional (sensor externo) |
| **Energía** | 12–24 VDC · batería backup (hasta 10 días) |
| **Ambiente** | IP67 · −40 °C / +70 °C |
| **Dimensiones** | ⚠️ 7.5 × 4.3 × 1.5 in — convertir a mm: ~190 × 109 × 38 mm |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable remoto |
| **Respaldo** | Sí (hasta 10 días) |
| **Certificaciones** | PTCRB · CE |

**I/O:**
- Carrier Transicold interface
- Thermo King interface
- Up to 3 temperature probes
- Door sensor (optional)
- Fuel sensor (optional)
- GPS sensor (optional)

**Puertos:**
- RS-232
- RS-485
- BLE 5.0

**Hotspots:**
1. `(48%, 35%)` → Interfaz directa Carrier Transicold y Thermo King → apunta a `io`
2. `(74%, 55%)` → RS-232 / RS-485 + BLE 5.0 para sensores externos → apunta a `ports`
3. `(25%, 62%)` → Enclosure IP67 — polycarbonate rugged → apunta a `env`

**Documentos:**
- Datasheet: PT6100_VIACHAIN.pdf (en)
- Installation Guide: PT6000 Installation guide.pdf

---

## PRODUCTO 11 · PT 7000

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `pt7000` |
| **Nombre** | PT 7000 |
| **Categoría** | Telemática para maquinaria pesada |
| **Role** | Telemática celular o dual satélite-celular para construcción y maquinaria. |
| **Hero** | ⚠️ EN INGLÉS: "Cellular or dual mode satellite-cellular telematics for heavy equipment and construction." — TRADUCIR |
| **Descripción** | El PT 7000 rastrea equipos pesados en los entornos más exigentes. Conectividad 4G LTE con fallback 3G/2G y conectividad satelital opcional (IDP o OGx). Múltiples interfaces: 3-wire, CAN Bus, RS-232 (×2), 4 entradas digitales, 4 entradas analógicas, Bluetooth. Batería backup: 30 días. |
| **Satelital** | Opcional (IDP / OGx) |
| **Celular** | 4G LTE · 3G/2G fallback |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo |
| **Energía** | 9–32 VDC · batería backup (hasta 30 días) |
| **Ambiente** | IP67 · SAE J1455 · −40 °C / +85 °C |
| **Dimensiones** | ⚠️ 7.5 × 4.3 × 1.5 in — convertir a mm: 19.1 × 10.9 × 3.8 cm = ~191 × 109 × 38 mm |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable OTA / RS-232 |
| **Respaldo** | Sí (hasta 30 días) |
| **Certificaciones** | PTCRB · FCC · IC · CE · Inmarsat · RoHS |

**I/O:**
- 3-axis accelerometer
- 4× digital input
- 2× digital output (pull-up)
- 2× digital output (pull-down)
- 4× analog input
- 3-Wire
- CAN Bus (×2)

**Puertos:**
- 2× RS-232
- CAN Bus
- Bluetooth
- RF FAKRA SMB
- Baseband Delphi 24-pin

**Hotspots:**
1. `(48%, 35%)` → Antenas celular y satelital externas — FAKRA SMB → apunta a `satellite`
2. `(72%, 55%)` → CAN Bus (×2) + RS-232 (×2) para datos del motor → apunta a `io`
3. `(25%, 62%)` → Batería backup 30 días · IP67 polycarbonate → apunta a `power`

**Documentos:**
- Datasheet: PT7000_VIACHAIN.pdf (en)
- Hardware Guide: PT113 PT 7000 Hardware Guide.pdf
- Installation Guide: PT107 PT 7000 Installation Guide.pdf

---

## PRODUCTO 12 · SC 1000 (versión VIACHAIN) ⚠️ DUPLICADO SKYWAVE

| Campo | Valor actual (ES) — versión VIACHAIN |
|-------|---------------------------------------|
| **ID** | `sc1000` |
| **Nombre** | SC 1000 |
| **Categoría** | Rastreador solar satelital |
| **Role** | Rastreo satelital solar para activos en cualquier rincón del mundo. |
| **Hero** | ⚠️ EN INGLÉS: "Solar-powered satellite tracking for intelligent visibility." — TRADUCIR |
| **Descripción** | El SC 1000 utiliza la red satelital OGx de ORBCOMM para entregar visibilidad en activos ubicados en áreas remotas sin cobertura celular. Batería solar, instalación sin cables y soporte para sensores BLE. ATEX Zona 2. IP67 + IPX9K para los entornos más hostiles del planeta. |
| **Satelital** | OGx two-way global |
| **Celular** | — |
| **GNSS** | GPS · GLONASS · BeiDou · Galileo |
| **Energía** | Solar (panel integrado) · batería Li-ion |
| **Ambiente** | IP67 · IPX9K · −30 °C / +70 °C · ATEX Zone 2 |
| **Dimensiones** | 224 × 76 × 28 mm |
| **Memoria** | ⚠️ VACÍO — completar |
| **Programación** | Configurable OTA |
| **Respaldo** | Sí (batería) |
| **Certificaciones** | FCC/IC · CE RED 2014/53/EU · ATEX Zone 2 · CID2 · Inmarsat · ROHS · WEEE |

**I/O:**
- 3-axis accelerometer
- Device temperature sensor
- BLE door sensor support

**Puertos:**
- BLE 5.2

**Hotspots:**
1. `(46%, 28%)` → Panel solar integrado + batería Li-ion recargable → apunta a `power`
2. `(70%, 48%)` → Antena satelital OGx — cobertura global bidireccional → apunta a `satellite`
3. `(22%, 68%)` → IP67 + IPX9K · ATEX Zona 2 · C1D2-Group CD T6 — entornos extremos → apunta a `certs`

---

# ACCESORIOS VIACHAIN

---

## ACC-VC-01 · CS 500

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `cs500` |
| **Nombre** | CS 500 |
| **Categoría** | Sensor de cámara de carga |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El CS 500 monitorea y captura imágenes del estado interno de la carga en tráilers. Detecta apertura de puertas, inicio/fin de movimiento. Compatible con GT 1210, GT 1220 y GT 1230 mediante conector 16-pin (requerido). Incluye cámara, brackets y cable de 18m. Kit de cubierta de cable en ángulo recto recomendado. Almacena hasta 1000 imágenes localmente. |
| **Compatible con** | GT 1210 · GT 1220 · GT 1230 (requiere conector 16-pin) |
| **Dimensiones** | 7.7 × 9.2 × 2.5 cm · 95 g |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Puertos** | RS-485 |

---

## ACC-VC-02 · CS 511

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `cs511` |
| **Nombre** | CS 511 |
| **Categoría** | Sensor de cámara con solar |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El CS 511 es una cámara de carga solar con sensor de puerta integrado y acelerómetro. Compatible con GT 1220 y GT 1230. Captura automática y manual. Batería Li-ion 2500 mAh. |
| **Compatible con** | GT 1220 · GT 1230 |
| **Dimensiones** | 233 × 75 × 25 mm · 350 g |
| **Ambiente** | IP67 · −40 °C / +70 °C |
| **Puertos** | BLE 5.0 |

---

## ACC-VC-03 · DS 100

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `ds100` |
| **Nombre** | DS 100 |
| **Categoría** | Sensor de puerta cableado |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor magnético hermético para monitorear puertas de tráileres y contenedores. Detección de apertura no autorizada fuera de geocercas. Configuración OTA. Compatible con GT 1100. |
| **Compatible con** | GT 1100 ⚠️ Producto legacy — verificar si está vigente |
| **Dimensiones** | 3" × 1.25" × 0.5" |
| **Ambiente** | −30 °C / +70 °C |
| **Puertos** | Cableado |

---

## ACC-VC-04 · DS 300 ⚠️ TAMBIÉN EN SKYWAVE

| Campo | Valor actual (ES) — versión VIACHAIN |
|-------|---------------------------------------|
| **ID** | `ds300` |
| **Nombre** | DS 300 |
| **Categoría** | Sensor de puerta inalámbrico |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor de puerta inalámbrico BLE para tráileres y contenedores. Sin cables, fácil instalación. Batería Li-ion 1000 mAh, hasta 5 años de operación. Compatible con CT 1000, ST 9100, GT 1100/1200/1220. |
| **Compatible con** | CT 1000 · ST 9100 · GT 1100 · GT 1200 · GT 1220 |
| **Dimensiones** | 102 × 64 × 15 mm · 97 g |
| **Ambiente** | IP67 · −40 °C / +85 °C |
| **Puertos** | BLE 4.0 |

---

## ACC-VC-05 · DS 310 ⚠️ TAMBIÉN EN SKYWAVE

| Campo | Valor actual (ES) — versión VIACHAIN |
|-------|---------------------------------------|
| **ID** | `ds310` |
| **Nombre** | DS 310 |
| **Categoría** | Sensor de puerta inalámbrico avanzado |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El DS 310 es el sensor de puerta inalámbrico de nueva generación de VIACHAIN, diseñado específicamente para condiciones intermodales exigentes. Mayor cobertura BLE 5.1, mejor eficiencia energética y conectividad mejorada detectan aperturas y cierres con mayor precisión y sin falsas alarmas. Diseño compacto y de bajo perfil IP67 para áreas de alto tráfico. Sin cables ni antenas externas. |
| **Compatible con** | GT 1200 · GT 1210 · GT 1220 · GT 1230 · CT 5300 |
| **Dimensiones** | 102 × 64 × 15 mm · 97 g |
| **Ambiente** | IP67 · SAE J1455 · MIL-STD-810G · −40 °C / +85 °C |
| **Puertos** | BLE 5.1 |

---

## ACC-VC-06 · TS 310 ⚠️ TAMBIÉN EN SKYWAVE

| Campo | Valor actual (ES) — versión VIACHAIN |
|-------|---------------------------------------|
| **ID** | `ts310` |
| **Nombre** | TS 310 |
| **Categoría** | Sensor de temperatura inalámbrico |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor inalámbrico BLE para monitoreo de temperatura en carga sensible. Cumple EN 12830:2018. IP69k. Precisión ±0.5°C. Batería Li-ion 1000 mAh, 5 años de vida mínima. Compatible con RT 8000, Euroscan MX2, ST 9100. |
| **Compatible con** | RT 8000 · RT 8110 · RT 8120 · Euroscan MX2 · ST 9100 series |
| **Dimensiones** | 136 × 64 × 21 mm · 120 g |
| **Ambiente** | IP69k · −40 °C / +85 °C |
| **Puertos** | BLE 5.1 |

---

## ACC-VC-07 · IS 400

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `is400` |
| **Nombre** | IS 400 |
| **Categoría** | Sensor ID de tractor |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El IS 400 elimina el error de conectar el tractor equivocado al tráiler. Emparejamiento automático BLE. Batería 2700 mAh con vida de 7+ años. Compatible con toda la familia GT 12xx y PT 6100. |
| **Compatible con** | GT 1200 · GT 1210 · GT 1220 · GT 1230 · PT 6100 |
| **Dimensiones** | 16.9 × 2.6 × 7.4 cm · 190 g |
| **Ambiente** | IP69K · −40 °C / +85 °C |
| **Puertos** | BLE 5.0 |

---

## ACC-VC-08 · ST 2100 (accesorio satélite VIACHAIN)

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `st2100` |
| **Nombre** | ST 2100 |
| **Categoría** | Módem satelital |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El ST 2100 agrega conectividad satelital bidireccional global a dispositivos VIACHAIN en zonas remotas sin cobertura celular. Compatible con las redes OGx e IsatData Pro (IDP) de ORBCOMM. GNSS integrado (GPS/GLONASS/BeiDou, precisión 2.0 m). Alimentación 9–32 VDC. Comunicación con el dispositivo principal vía RS-232 y set de comandos AT. Diseño sellado IP67 para vehículos, vagones y activos fijos industriales. |
| **Compatible con** | GT 1030 · GT 1200 · GT 1210 · GT 1220 · GT 1230 · PT 7000 |
| **Dimensiones** | 125 × 85 × 36 mm |
| **Ambiente** | IP67 · SAE J1455 · MIL-STD-810G · −40 °C / +85 °C |
| **Puertos** | RS-232 (AT command set) · OGx · IsatData Pro (IDP) |

---

## ACC-VC-09 · FS 210

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `fs210` |
| **Nombre** | FS 210 |
| **Categoría** | Sensor de combustible reefer |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | El FS 210 monitorea el nivel de combustible del reefer con precisión, detecta pérdida rápida de combustible y alerta sobre riesgo de robo. Compatible con tanques de 25 a 500 galones. Zona 1 (Clase I, División 1). |
| **Compatible con** | Carrier reefers · Thermo King reefers |
| **Dimensiones** | 5" × 1.75" diámetro |
| **Ambiente** | IP67 · −40 °C / +70 °C |
| **Puertos** | Delphi Standard 150 (3-pin) |

---

## ACC-VC-10 · Euroscan Back Door

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `euroscan_back` |
| **Nombre** | Euroscan Back Door |
| **Categoría** | Sensor puerta trasera |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor de puerta trasera Euroscan para activos de transporte refrigerados y secos. Tolera desalineación, floormontable, aguanta el paso de vehículos. Alertas de apertura no autorizada y fuera de geocercas. IP67. |
| **Compatible con** | Euroscan MX2 · Euroscan X3 · GT 1100 series · PT 6000 ⚠️ Productos legacy |
| **Dimensiones** | Imán: 60×35×55mm · Sensor: 135×42×12mm |
| **Ambiente** | IP67 · −10/−40 °C / +70 °C |
| **Puertos** | Cableado |

---

## ACC-VC-11 · Euroscan Side Door

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `euroscan_side` |
| **Nombre** | Euroscan Side Door |
| **Categoría** | Sensor puerta lateral |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor de puerta lateral Euroscan para activos de transporte. Detección de apertura lateral, alertas configurables y soporte para múltiples activos. Compatible con Euroscan MX2/X3 y VIACHAIN GT 1100. |
| **Compatible con** | Euroscan MX2 · Euroscan X3 · GT 1100 series ⚠️ Producto legacy |
| **Dimensiones** | — |
| **Ambiente** | IP67 |
| **Puertos** | Cableado |

---

## ACC-VC-12 · Euroscan Temp

| Campo | Valor actual (ES) |
|-------|-------------------|
| **ID** | `euroscan_temp` |
| **Nombre** | Euroscan Temp |
| **Categoría** | Sensor temperatura Euroscan |
| **Hero** | ⚠️ EN INGLÉS — TRADUCIR |
| **Descripción** | Sensor de temperatura Euroscan para activos de cadena de frío. Alta precisión, compatible con unidades Euroscan MX2/X3 y VIACHAIN GT 1100 series. |
| **Compatible con** | Euroscan MX2 · Euroscan X3 · GT 1100 series ⚠️ Producto legacy |
| **Dimensiones** | — |
| **Ambiente** | IP67 |
| **Puertos** | Cableado |

---

# RESUMEN DE PENDIENTES

## A. Heroes en inglés a traducir (VIACHAIN):
| Producto | Hero actual (EN) | Hero en español propuesto |
|----------|-----------------|---------------------------|
| CT 1000 | "Simpler tracking. Smarter decisions." | "Rastreo simple. Decisiones inteligentes." |
| CT 1010 | "Visibility. Security. Protection." | "Visibilidad. Seguridad. Protección." |
| CT 3600 | "Remote reefer container management and control." | "Gestión y control remoto de contenedores reefer." |
| CT 5300 | "Advanced asset intelligence with integrated cargo camera." | "Inteligencia avanzada de activos con cámara de carga integrada." |
| GT 1030 | "Complete visibility of transportation assets, chassis, flatbeds and more." | "Visibilidad completa de activos de transporte, chasis, plataformas y más." |
| GT 1200 | "Next-generation solar-powered tracking for dry trailers, containers and transport assets." | "Rastreo solar de nueva generación para tráilers secos, contenedores y activos de transporte." |
| GT 1210 | "Next-generation, all-in-one solar-powered tracking device with integrated cargo sensor." | "Dispositivo todo-en-uno solar de nueva generación con sensor de carga integrado." |
| GT 1220 | "Solar-powered tracking for dry trailers, containers and other transport assets." | "Rastreo solar para tráilers secos, contenedores y otros activos de transporte." |
| GT 1230 | "Advanced technology for ease and reliability in transport asset tracking." | "Tecnología avanzada para rastreo fácil y confiable de activos de transporte." |
| PT 6100 | "Complete reefer monitoring and control." | "Monitoreo y control completo de reefer." |
| PT 7000 | "Cellular or dual mode satellite-cellular telematics for heavy equipment and construction." | "Telemática celular o dual satélite-celular para maquinaria pesada y construcción." |
| SC 1000 (VC) | "Solar-powered satellite tracking for intelligent visibility." | "Rastreo satelital solar para visibilidad inteligente." |

## B. Accesorios con heroes en inglés a traducir (VIACHAIN):
CS 500, CS 511, DS 100, DS 300, DS 310, TS 310, IS 400, ST 2100, FS 210, Euroscan Back/Side/Temp

## C. Datos vacíos / incompletos a completar:
| Producto | Campo(s) pendiente(s) |
|----------|----------------------|
| ST 2100 | Dimensiones, Memoria, Certificaciones |
| ST 4000 | Dimensiones, Memoria, Certificaciones |
| ST 4300 | Memoria, Certificaciones, Datasheets |
| ST 6000 | Dimensiones, Memoria, Certificaciones |
| SC 1000 (SK) | Memoria, Certificaciones |
| ST 9100 | Campo `memory` contiene batería, no memoria — aclarar |
| CT 5300 | Memoria |
| GT 1030 | Memoria |
| PT 6100 | Memoria, Dimensiones en mm |
| PT 7000 | Memoria, Dimensiones en mm |
| SC 1000 (VC) | Memoria |
| TS 310 (SK) | Dimensiones, ambiente completo, especificaciones técnicas |

## D. Decisiones arquitecturales requeridas:
1. **SC 1000** — ¿mantener como producto separado en ambas marcas con contextos distintos, o unificar datos?
2. **TS 310 / DS 300 / DS 310** — ¿un único accesorio compartido o fichas separadas por marca?
3. **GT 1200/1210/1220/1230** — ¿tienen imagen individual o comparten GT12XX-VIACHAIN.png?
4. **DS 100 y Euroscan** — ¿están vigentes o son legacy para remover del showroom?
5. **Dimensiones PT 6100 / PT 7000** — ¿estandarizar a mm o mantener pulgadas?

---
*Fin del documento de revisión — v1.0 · 2026-11-10*
