# Guía de Git y GitHub

**Estrategia:** una rama por Issue (`feature/issue-NN-nombre`) creada desde `main`; cada integrante hace sus commits ahí y abre un Pull Request (PR) hacia `main` que **otro integrante revisa**. `main` solo recibe código que funciona.

## 1. Inicializar (lo hace el Integrante 1, una sola vez)
```bash
cd toolboard
git init -b main
git add . && git commit -m "chore: estructura inicial de ToolBoard"
git remote add origin https://github.com/USUARIO/toolboard.git
git push -u origin main
```
Agregar a los otros cuatro como colaboradores (GitHub > Settings > Collaborators). Los demás: `git clone https://github.com/USUARIO/toolboard.git`.

## 2. Crear los 10 Issues
En GitHub > Issues > New issue, uno por cada sección de `docs/ISSUES.md` (título + texto), asignando al responsable. O con GitHub CLI: `gh issue create --title "Issue 3: Inventario y categorías" --body-file ... --assignee USUARIO`.

## 3. Trabajar en una rama
```bash
git checkout main && git pull
git checkout -b feature/issue-03-inventario
# ...programar...
git add inventario.php
git commit -m "feat(inventario): alta, edición y baja de herramientas (#3)"
git push -u origin feature/issue-03-inventario
```
**Commits sugeridos:** `feat(estructura): conexión PDO y funciones comunes (#1)` · `feat(dashboard): tarjetas y alertas (#2)` · `feat(inventario): CRUD de herramientas (#3)` · `feat(inventario): búsqueda y filtros (#4)` · `feat(personal): CRUD de personal (#5)` · `feat(prestamos): registro con control de stock (#6)` · `feat(devoluciones): estado al devolver e incidencias (#7)` · `feat(mantenimiento): estados y cierre (#8)` · `feat(historial): historial y reportes (#9)` · `docs: README y pruebas (#10)`.

## 4. Pull Request
En GitHub: *Compare & pull request* → base `main`, descripción con `Closes #3`. Un compañero revisa, comenta y aprueba; luego **Merge**. Tras cada merge, todos: `git checkout main && git pull`, probar en XAMPP y actualizar su rama: `git checkout mi-rama && git merge main`.

## 5. Conflictos
Al hacer `git merge main` puede aparecer `CONFLICT`. Abrir el archivo, buscar `<<<<<<<`, `=======`, `>>>>>>>`, dejar el código correcto de ambos lados y borrar las marcas; después:
```bash
git add archivo && git commit -m "merge: resolver conflicto en archivo"
```
Para evitarlos: cada persona edita su archivo de módulo; los cambios en `functions.php` o `style.css` se avisan al grupo.

## 6. Verificación final de main
```bash
git checkout main && git pull && git log --oneline --graph
```
Reimportar `database/toolboard.sql`, ejecutar `tests/pruebas_manuales.md` y comprobar en `git shortlog -sn` que los cinco tienen commits. Cerrar con una etiqueta: `git tag v1.0 && git push --tags`.
