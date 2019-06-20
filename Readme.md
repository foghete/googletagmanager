# Módulo para instalar Google Tag Manager en Prestshop 1.6

Este módulo, además de insertar el código necesario para usar GTM, crea una variable en la dataLayer que nos permite segmentar en Google Analytics las compras de nuevos clientes. Con este dato podemos calcular el coste de adquisición por cliente tal y como se explica [en este post](https://foghete.com/como-calcular-el-cac-en-prestashop-con-google-analytics/)

Para configurarlo debes editar las plantillas de Smarty de la carpeta views y sustituir el ID de contendor de Google Tag Manager que deseas usar.