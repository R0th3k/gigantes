<template>
  <section class="partidos-proximos">
    <div class="container">
      
      
      <div v-if="loading" class="text-center py-5">
        <p>Cargando partidos...</p>
      </div>
      
      <div v-else-if="error" class="text-center py-5">
        <p class="text-danger">Error al cargar los partidos</p>
      </div>
      
      <div v-else class="swiper partidos-swiper">
        <div class="swiper-wrapper">
          <div 
            v-for="(partido, index) in partidosLimitados" 
            :key="index"
            class="swiper-slide"
          >
            <div class="partido-card">
              <div class="partido-header">
                <div class="partido-fecha">
                  {{ formatearFecha(partido.fecha) }}, {{ partido.hora }} Hrs
                </div>
              </div>
              
              <div class="partido-content">
                <div class="equipo equipo-local">
                  <div class="equipo-logo">
                    <img 
                      :src="getLogoUrl(partido.logo_local)" 
                      :alt="partido.equipo_local"
                      @error="handleImageError"
                    />
                  </div>
                  <div class="equipo-nombre">{{ partido.equipo_local }}</div>
                  <div v-if="yaPaso(partido)" class="equipo-puntos">
                    {{ partido.puntos_local !== null ? partido.puntos_local : '-' }}
                  </div>
                </div>
                
                <div class="vs">VS</div>
                
                <div class="equipo equipo-visitante">
                  <div class="equipo-logo">
                    <img 
                      :src="getLogoUrl(partido.logo_visitante)" 
                      :alt="partido.equipo_visitante"
                      @error="handleImageError"
                    />
                  </div>
                  <div class="equipo-nombre">{{ partido.equipo_visitante }}</div>
                  <div v-if="yaPaso(partido)" class="equipo-puntos">
                    {{ partido.puntos_visitante !== null ? partido.puntos_visitante : '-' }}
                  </div>
                </div>
              </div>
              
              <div class="partido-footer">
                <div class="partido-sede">{{ partido.sede }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  cantidad: {
    type: Number,
    default: 6
  },
  proximos: {
    type: Boolean,
    default: false
  },
  anteriores: {
    type: Boolean,
    default: false
  }
});

// Validar que solo una prop esté activa
if (props.proximos && props.anteriores) {
  console.warn('PartidosProximos: No se pueden usar "proximos" y "anteriores" al mismo tiempo. Se usará "proximos" por defecto.');
}

const partidos = ref([]);
const loading = ref(true);
const error = ref(false);
const swiperInstance = ref(null);

// Función para crear una fecha en zona horaria local desde fecha y hora en formato CST
const crearFechaCST = (fecha, hora) => {
  // Parsear fecha y hora manualmente: "2026-01-17" y "20:00"
  const [año, mes, dia] = fecha.split('-');
  const [horas, minutos] = hora.split(':');
  
  // Crear fecha usando los componentes directamente (se interpretará en zona horaria local)
  // Esto evita que JavaScript interprete la fecha como UTC
  return new Date(
    parseInt(año),
    parseInt(mes) - 1, // Los meses en Date son 0-indexados
    parseInt(dia),
    parseInt(horas),
    parseInt(minutos)
  );
};

// Función para verificar si un partido ya pasó
const yaPaso = (partido) => {
  const ahora = new Date();
  const fechaHoraPartido = crearFechaCST(partido.fecha, partido.hora);
  return fechaHoraPartido < ahora;
};

// Función para verificar si un partido es futuro
const esFuturo = (partido) => {
  const ahora = new Date();
  const fechaHoraPartido = crearFechaCST(partido.fecha, partido.hora);
  return fechaHoraPartido >= ahora;
};

const partidosFiltrados = computed(() => {
  let partidosFiltrados = partidos.value;
  
  // Filtrar según la prop activa
  if (props.proximos && !props.anteriores) {
    partidosFiltrados = partidos.value.filter(esFuturo);
  } else if (props.anteriores && !props.proximos) {
    partidosFiltrados = partidos.value.filter(yaPaso);
  }
  
  return partidosFiltrados;
});

const partidosLimitados = computed(() => {
  return partidosFiltrados.value.slice(0, props.cantidad);
});

const cargarPartidos = async () => {
  try {
    loading.value = true;
    error.value = false;
    
    // Cargar axios desde CDN si no está disponible
    if (typeof axios === 'undefined') {
      await loadScript('https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js');
    }
    
    const response = await axios.get('/assets/data/partidos_t26.json');
    partidos.value = response.data;
  } catch (err) {
    console.error('Error al cargar partidos:', err);
    error.value = true;
  } finally {
    loading.value = false;
  }
};

const loadScript = (src) => {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = src;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const formatearFecha = (fecha) => {
  // Parsear la fecha manualmente para evitar problemas de zona horaria
  // La fecha viene en formato "YYYY-MM-DD" y debe interpretarse en GMT-6 (CST)
  const [año, mes, dia] = fecha.split('-');
  const meses = [
    'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
    'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
  ];
  return `${dia} de ${meses[parseInt(mes) - 1]} ${año}`;
};

const getLogoUrl = (logo) => {
  // Si el logo es null o está vacío, usar un placeholder
  if (!logo || logo === 'null' || logo === '') {
    return '/assets/images/logo.svg';
  }
  // Construir la ruta usando el logo del JSON
  return `/assets/images/equipos/${logo}.png`;
};

const handleImageError = (event) => {
  // Si falla la carga, usar el logo por defecto
  event.target.src = '/assets/images/logo.svg';
};

const initSwiper = () => {
  if (typeof Swiper === 'undefined') {
    loadScript('https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js').then(() => {
      initSwiperInstance();
    });
  } else {
    initSwiperInstance();
  }
};

const initSwiperInstance = () => {
  if (swiperInstance.value) {
    swiperInstance.value.destroy();
  }
  
  swiperInstance.value = new Swiper('.partidos-swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: false,
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
    },
  });
};

watch(() => partidosLimitados.value.length, () => {
  if (partidosLimitados.value.length > 0) {
    setTimeout(() => {
      initSwiper();
    }, 100);
  }
});

onMounted(() => {
  // Cargar CSS de Swiper
  if (!document.querySelector('link[href*="swiper"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
    document.head.appendChild(link);
  }
  
  cargarPartidos();
});
</script>

<style scoped>
.section-title {
  font-family: "Bebas Neue", sans-serif;
  font-size: 3rem;
  text-transform: uppercase;
  text-align: center;
  margin-bottom: 3rem;
  color: var(--bs-primary);
  letter-spacing: 2px;
}

.partidos-swiper {
  padding-bottom: 50px;
}

.partido-card {
  background: var(--bs-primary);
background: radial-gradient(circle,rgba(30, 115, 168, 1) 0%, rgba(13, 57, 99, 1) 50%);
  border-radius: 12px;
  border: 1px solid #185E8F;
  padding: 2rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.partido-card:hover {
  
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.partido-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.partido-fecha {
  font-family: "Arimo", sans-serif;
  font-size: 0.9rem;
  opacity: 0.9;
}

.partido-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 2rem 0;
  gap: 1rem;
}

.equipo {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.equipo-logo {
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  padding: 10px;
}

.equipo-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.equipo-nombre {
  font-family: "Bebas Neue", sans-serif;
  font-size: 1.2rem;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.equipo-puntos {
  font-family: "Bebas Neue", sans-serif;
  font-size: 2rem;
  font-weight: bold;
  color: var(--bs-warning);
  margin-top: 0.5rem;
  text-align: center;
}

.vs {
  font-family: "Bebas Neue", sans-serif;
  font-size: 2rem;
  font-weight: bold;
  color: var(--bs-warning);
  padding: 0 1rem;
}

.partido-footer {
  text-align: center;
  margin-top: auto;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.partido-sede {
  font-family: "Arimo", sans-serif;
  font-size: 0.9rem;
  opacity: 0.9;
}

.swiper-pagination {
  bottom: 0 !important;
}

.swiper-pagination-bullet {
  background: var(--bs-primary);
  opacity: 0.5;
}

.swiper-pagination-bullet-active {
  opacity: 1;
  background: var(--bs-warning);
}

.swiper-button-next,
.swiper-button-prev {
  color: var(--bs-primary);
}

.swiper-button-next:after,
.swiper-button-prev:after {
  font-size: 24px;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
  color: var(--bs-warning);
}

@media (max-width: 767.98px) {
  .section-title {
    font-size: 2rem;
  }
  
  .partido-content {
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .vs {
    font-size: 1.5rem;
    padding: 0.5rem 0;
  }
  
  .equipo-logo {
    width: 80px;
    height: 80px;
  }
  
  .equipo-nombre {
    font-size: 1rem;
  }
}
</style>

