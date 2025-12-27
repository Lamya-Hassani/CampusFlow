# Guide des Styles - CampusFlow Version 2

## 📍 Où se trouvent les styles ?

### 1. **Template de Base** - `templates/base.html.twig`
C'est le fichier principal qui charge Tailwind CSS pour TOUTES les pages du projet.

**Ligne 9** : Chargement de Tailwind CSS v3.4.1 via CDN
```twig
<script src="https://cdn.tailwindcss.com/3.4.1"></script>
```

**Ce fichier contient :**
- La navigation principale (lignes 18-46)
- Le style du body (ligne 16) : `bg-gray-50 min-h-screen`
- Les styles des messages flash (lignes 50-60)
- La structure de base de toutes les pages

### 2. **Fichier CSS** - `assets/styles/app.css`
⚠️ **Actuellement vide** - On utilise Tailwind CSS via CDN, pas de CSS compilé local.

### 3. **Templates individuels** - `templates/`
Chaque template utilise les classes Tailwind directement dans le HTML.

#### Structure des templates :
```
templates/
├── base.html.twig          ← Style de base (navigation, layout)
├── security/
│   └── login.html.twig     ← Page de connexion (avec animations personnalisées)
├── admin/
│   ├── dashboard.html.twig ← Dashboard admin
│   ├── student/            ← Pages CRUD étudiants
│   ├── teacher/            ← Pages CRUD enseignants
│   ├── classe/             ← Pages CRUD classes
│   ├── subject/            ← Pages CRUD matières
│   └── schedule/           ← Pages CRUD emploi du temps
├── teacher/
│   └── dashboard.html.twig ← Dashboard enseignant
└── student/
    └── dashboard.html.twig ← Dashboard étudiant
```

## 🎨 Système de Styles Utilisé

### **Tailwind CSS v3.4.1**
- Chargé via CDN (pas besoin de compilation)
- Classes utilitaires directement dans le HTML
- Documentation : https://tailwindcss.com/docs

### **Couleurs principales utilisées :**
- **Indigo** : `bg-indigo-600`, `text-indigo-600` (couleur principale)
- **Gray** : `bg-gray-50`, `text-gray-900` (arrière-plans et textes)
- **Green** : `bg-green-100` (messages de succès)
- **Red** : `bg-red-100` (messages d'erreur)

## 📝 Comment modifier les styles ?

### Option 1 : Modifier un template spécifique
Exemple : Pour changer le style de la page login
→ Éditer `templates/security/login.html.twig`

### Option 2 : Modifier le style global
Pour changer la navigation ou le style de base
→ Éditer `templates/base.html.twig`

### Option 3 : Ajouter du CSS personnalisé
1. Créer un fichier CSS dans `assets/styles/`
2. L'ajouter dans `templates/base.html.twig` dans le bloc `{% block stylesheets %}`

## 🎯 Classes Tailwind les plus utilisées

### Layout
- `container` : Conteneur centré
- `max-w-7xl mx-auto` : Largeur maximale centrée
- `flex` : Flexbox
- `grid` : Grid layout

### Espacements
- `p-4`, `px-6`, `py-4` : Padding
- `m-4`, `mx-auto`, `my-2` : Margin
- `space-y-4` : Espacement vertical entre enfants

### Couleurs
- `bg-indigo-600` : Fond indigo
- `text-white` : Texte blanc
- `hover:bg-indigo-700` : Effet au survol

### Bordures & Ombres
- `rounded-lg` : Bordures arrondies
- `shadow-lg` : Ombre
- `border` : Bordure

### Typographie
- `text-xl`, `text-2xl`, `text-3xl` : Tailles de texte
- `font-bold`, `font-semibold` : Graisses de police

## 📂 Fichiers à modifier selon vos besoins

| Besoin | Fichier à modifier |
|--------|-------------------|
| Style global (navigation, layout) | `templates/base.html.twig` |
| Page de connexion | `templates/security/login.html.twig` |
| Dashboard Admin | `templates/admin/dashboard.html.twig` |
| Liste des étudiants | `templates/admin/student/index.html.twig` |
| Formulaire étudiant | `templates/admin/student/new.html.twig` |
| Dashboard Enseignant | `templates/teacher/dashboard.html.twig` |
| Dashboard Étudiant | `templates/student/dashboard.html.twig` |

## 💡 Astuce

Pour voir tous les styles utilisés dans un template :
1. Ouvrir le fichier `.twig` correspondant
2. Les classes Tailwind sont dans les attributs `class="..."`
3. Utiliser la documentation Tailwind : https://tailwindcss.com/docs

## 🔧 Personnalisation des couleurs

Pour changer les couleurs principales du projet, cherchez et remplacez dans les templates :
- `indigo-600` → votre couleur
- `indigo-700` → votre couleur (hover)
- `purple-600` → votre couleur (si utilisé)

Exemple pour changer en bleu :
- `bg-indigo-600` → `bg-blue-600`
- `text-indigo-600` → `text-blue-600`

