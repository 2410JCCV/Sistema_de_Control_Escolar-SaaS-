# 🔧 Solución: Error de Conexión Rechazada

## El Problema
Error: `ERR_CONNECTION_REFUSED` - "La página localhost ha rechazado la conexión"

Esto significa que **Apache (el servidor web) NO está corriendo** en WAMP.

---

## ✅ Solución Paso a Paso

### 1. Verificar el Estado de WAMP

**Busca el ícono de WAMP en la bandeja del sistema:**
- 🔴 **Rojo**: Ningún servicio está corriendo
- 🟠 **Naranja**: Algunos servicios están corriendo, pero no todos
- 🟢 **Verde**: Todos los servicios están corriendo ✅

### 2. Si el ícono está ROJO o NARANJA:

1. **Haz clic derecho** en el ícono de WAMP
2. Selecciona **"Start All Services"** o **"Iniciar todos los servicios"**
3. Espera a que el ícono se vuelva **VERDE**

### 3. Si el ícono NO aparece:

1. Abre WAMP desde el menú de inicio de Windows
2. Busca "WampServer" en el menú
3. Haz clic en "WampServer" para iniciar la aplicación

### 4. Verificar que Apache esté corriendo:

1. Haz clic derecho en el ícono de WAMP
2. Ve a **"Tools"** → **"Test Port 80"**
3. Si dice que el puerto está ocupado, necesitas liberarlo

### 5. Verificar los servicios manualmente:

1. Haz clic derecho en el ícono de WAMP
2. Ve a **"Tools"** → **"Check Service Status"**
3. Verifica que Apache y MySQL estén marcados como "Started"

---

## 🚨 Problemas Comunes

### Problema 1: Puerto 80 ocupado
**Síntoma:** Apache no inicia aunque lo intentes

**Solución:**
1. Haz clic derecho en WAMP → **"Tools"** → **"Check Port 80"**
2. Si está ocupado, identifica qué programa lo está usando
3. Cierra ese programa (Skype, IIS, etc.)
4. Reinicia WAMP

### Problema 2: Apache no inicia
**Síntoma:** El ícono se queda en naranja

**Solución:**
1. Abre el Log de Apache para ver el error
2. Haz clic derecho en WAMP → **"Tools"** → **"Apache"** → **"Apache Error Log"**
3. Revisa el último error y corrígelo

### Problema 3: MySQL no inicia
**Síntoma:** Puedes ver la página pero da error de base de datos

**Solución:**
1. Verifica que MySQL esté corriendo
2. Haz clic derecho en WAMP → **"Tools"** → **"MySQL"** → **"Service"** → **"Start/Resume Service"**

---

## ✅ Una vez que WAMP esté en VERDE:

1. Abre tu navegador
2. Ve a: `http://localhost/sistema_escolar/`
3. Deberías ver la pantalla de login

---

## 📞 Si Aún No Funciona:

1. **Reinicia WAMP completamente:**
   - Cierra WAMP (clic derecho → Exit)
   - Espera 10 segundos
   - Abre WAMP de nuevo

2. **Verifica la URL:**
   - Asegúrate de escribir: `http://localhost/sistema_escolar/`
   - NO uses: `https://` (solo http://)
   - NO uses: `localhost:8080` (a menos que hayas configurado Apache en ese puerto)

3. **Prueba el localhost básico:**
   - Ve a: `http://localhost/`
   - Deberías ver la página de WAMP
   - Si no la ves, Apache no está funcionando

---

## 🔍 Verificación Rápida

Ejecuta estos pasos en orden:

1. ✅ ¿El ícono de WAMP está VERDE?
2. ✅ ¿Puedes acceder a `http://localhost/`?
3. ✅ ¿Puedes acceder a `http://localhost/phpmyadmin/`?
4. ✅ ¿Puedes acceder a `http://localhost/sistema_escolar/`?

Si todos son ✅, entonces el sistema debería funcionar.



