# Sistema de Descuentos para Ventanas y Puertas de Aluminio

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

## 📌 Descripción General

Sistema web desarrollado en **Laravel + Livewire** para la **cotización, configuración y aplicación de descuentos** en la venta de **ventanas y puertas de aluminio**.

El sistema integra lógica técnica de fabricación con reglas comerciales, permitiendo a ventas y producción trabajar con la misma información.

---

## 🎯 Funcionalidades Principales

- Selección de sistemas de aluminio (Sistema Nova, Corredizas, Batientes, etc.)
- Configuración dinámica de medidas
- Generación de planos técnicos 2D
- Aplicación de descuentos automáticos y manuales
- Resumen de fabricación
- Exportación de cotización y plano en PDF

---

## ⚙️ Tecnologías Utilizadas

- **Laravel** – Backend
- **Livewire** – Interactividad sin JavaScript complejo
- **Tailwind CSS** – Diseño UI
- **Alpine.js** – Interacciones puntuales
- **Blade Components**
- **Exportación PDF desde HTML**

---

## 🧩 Módulos del Sistema

### 🔹 Selección de Sistema

Permite elegir el tipo de sistema a cotizar:

- Sistema Nova
- Persianas
- Doble Corrediza
- Batiente
- Proyectante

Cada sistema carga su propio componente Livewire, reiniciando el estado correctamente.

---

### 🔹 Configuración de Medidas

Campos dinámicos para:

- Ancho total
- Alto total
- Altura de puente / sobreluz
- Cantidad de hojas corredizas
- Cantidad de hojas fijas

Las medidas recalculan automáticamente la estructura del sistema.

---

### 🔹 Plano Técnico 2D

- Visualización proporcional
- Identificación de hojas (C / F)
- Medidas visibles
- Leyenda de colores
- Uso interno para ventas y fabricación

---

### 🔹 Sistema de Descuentos

Soporta múltiples reglas de descuento:

- Descuentos por volumen
- Descuentos por tipo de sistema
- Ajustes manuales
- Control de márgenes

Ejemplos:
- % de descuento por cantidad de hojas
- Precio especial para sistemas premium
- Ajustes autorizados por rol

---

### 🔹 Resumen de Fabricación

Muestra:

- Medidas finales ajustadas
- Cantidad de hojas
- Accesorios incluidos
- Detalle técnico de piezas
- Tabla lista para producción

---

### 🔹 Exportación a PDF

Incluye:

- Plano técnico
- Resumen de fabricación
- Medidas finales
- Observaciones

Generado directamente desde el HTML renderizado.

---

## 🛠️ Instalación

```bash
composer install
npm install
npm run dev
php artisan migrate
php artisan serve
