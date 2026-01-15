# Gigantes de Aguascalientes

Sitio web oficial del equipo de voleibol **Gigantes de Aguascalientes**. Sitio estático desarrollado con Astro, Bootstrap 5 y SCSS, que incluye blog de noticias, resultados de partidos, galería de fotos y formulario de contacto.

## 🏐 Sobre el Proyecto

Este sitio web presenta al equipo de voleibol Gigantes de Aguascalientes, mostrando información sobre próximos partidos, resultados, noticias del equipo y contenido multimedia. El sitio está optimizado para producción con sitemap, RSS y SEO configurado.

## 🚀 Características

- **Página de Inicio**: Hero con slides, próximos partidos y últimas noticias
- **Blog de Noticias**: Sistema de blog con paginación para noticias sobre voleibol en Aguascalientes
- **Resultados**: Visualización de resultados de partidos jugados
- **Galería**: Galería de imágenes con PhotoSwipe
- **Contacto**: Formulario de contacto con validación JavaScript y envío por PHP
- **Tienda**: Página temporal de próximamente
- **Redes Sociales**: Integración con Facebook, Instagram, TikTok y WhatsApp

## 📋 Requisitos

- Node.js 18+ (recomendado 20+)
- npm 9+
- Servidor PHP (para el formulario de contacto)

## 🛠️ Instalación

```bash
npm install
```

## 💻 Desarrollo

```bash
npm run dev
```

El servidor de desarrollo estará disponible en `http://localhost:4321`

## 🏗️ Build de Producción

```bash
npm run build
npm run preview
```

El build se genera en `dist/`. Para producción, sube el contenido de `dist/` a tu servidor web.

## 📁 Estructura del Proyecto

```
public/
  assets/          # Imágenes, logos, datos JSON de partidos
  send-mail.php    # Script PHP para envío de emails
  .htaccess        # Configuración de redirección HTTPS
  robots.txt       # Configuración SEO
src/
  components/      # Componentes Astro reutilizables
    - Hero.astro
    - Navbar.astro
    - Footer.astro
    - Noticias.astro
    - Loader.astro
    - SocialFloat.astro
  content/
    blog/          # Posts del blog en Markdown
  layouts/
    Main.astro     # Layout principal
  pages/           # Rutas del sitio
    - index.astro  # Página temporal de lanzamiento
    - inicio.astro # Página principal
    - blog/        # Blog y paginación
    - contacto.astro
    - resultados.astro
    - galeria.astro
    - tienda.astro
  scss/            # Estilos (Bootstrap desde SCSS + personalización)
  consts.ts        # Constantes del sitio (título, patrocinadores)
  utils/           # Utilidades y helpers
```

## 🎨 Tecnologías Utilizadas

- **Astro**: Framework para sitios estáticos
- **Bootstrap 5**: Framework CSS (compilado desde SCSS)
- **SCSS**: Preprocesador CSS con variables personalizadas
- **Vue.js**: Componentes interactivos (Partidos)
- **PhotoSwipe**: Galería de imágenes
- **Swiper**: Carrusel de slides
- **Font Awesome**: Iconos
- **PHP**: Backend para formulario de contacto

## 📧 Formulario de Contacto

El formulario de contacto (`/contacto`) utiliza `public/send-mail.php` para enviar emails a `hola@hektor.mx`. Asegúrate de configurar correctamente el servidor PHP y los permisos de envío de correo.

## 🔧 Configuración

### Variables del Sitio

Edita `src/consts.ts` para modificar:
- Título del sitio
- Descripción
- Lista de patrocinadores

### Estilos

Los estilos se personalizan en `src/scss/_variables.scss` donde puedes modificar colores, fuentes y otros valores de Bootstrap.

### SEO

- `astro.config.mjs` define `site` para URLs absolutas (sitemap, RSS, OG)
- `src/components/BaseHead.astro` añade metadatos SEO, canonical y Open Graph
- `public/robots.txt` publica el sitemap

## 📱 Redes Sociales

- Facebook: [Gigantes de Aguascalientes](https://www.facebook.com/GigantesDeAguascalientes)
- Instagram: [@gigantesdeaguascalientes](https://www.instagram.com/gigantesdeaguascalientes/)
- TikTok: [@gigantesdeaguascalientes](https://www.tiktok.com/@gigantesdeaguascalientes)

## 📝 Scripts Disponibles

- `npm run dev`: Inicia el servidor de desarrollo
- `npm run build`: Compila el sitio para producción
- `npm run preview`: Previsualiza el build de producción

## 🌐 Despliegue

1. Ejecuta `npm run build`
2. Sube el contenido de `dist/` a tu servidor web
3. Asegúrate de que el servidor tenga PHP habilitado para el formulario de contacto
4. Configura el `.htaccess` para redirección HTTPS (ya incluido)

## 📄 Licencia

Este proyecto es propiedad de Gigantes de Aguascalientes.

---

**Desarrollado con ❤️ para Gigantes de Aguascalientes**
