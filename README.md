# Beefeater CRUD Event Bundle
![Build Status](https://github.com/Beefeater83/beefeater-crud-event-bundle/actions/workflows/ci.yml/badge.svg)

**Beefeater CRUD Event Bundle** is a powerful Symfony bundle for rapid REST API generation with built-in support for CRUD operations, events, pagination, sorting, and filtering.

---

## 📦 Key Features

- 🔁 Auto-generation of CRUD routes (`Create`, `Read`, `Update`, `Delete`, `List`, `Patch`) based on YAML configuration  
- 📚 API versioning support (e.g., `v1`, `v2`)  
- 📄 Pagination, sorting, and filtering for `List` operations  
- 🧩 `before` and `after` events for `persist`, `update`, `delete`, `patch`, and `list`  
- ⚙️ Extensible via custom `EventListeners` (e.g., for logging, notifications, etc.)  
- ✅ Symfony Validator integration for request data validation  
- 🧠 Built-in support for Doctrine ORM  
- ✂️ Partial updates via `PATCH`  
- 🔀 Route parameters support including nested resources and UUIDs  

---

## 🔧 Installation



Install the bundle via Composer:

```bash
composer require beefeater/crud-event-bundle
```

---

## 🚀 Usage

### Register Routes

Add the following to `config/routes.yaml`:

```yaml
crud_api_v2:
    resource: '%kernel.project_dir%/config/crud_routes_v2.yaml'
    type: crud_routes
```

### Example: `crud_routes_v2.yaml`

```yaml
version: v2
resources:
  tournaments:
    entity: App\Entity\Tournament
    operations: [C, R, U, D, L, P]
    path: /tournaments

  categories:
    entity: App\Entity\Category
    operations: [C, R, U, D, L, P]
    path: /tournaments/{tournament}/categories
    params:
      tournament: App\Entity\Tournament
```

#### Routes generated for `tournaments`:

| Route Name              | Method | Path                        |
|-------------------------|--------|-----------------------------|
| api_v2_tournaments_C    | POST   | /api/v2/tournaments         |
| api_v2_tournaments_R    | GET    | /api/v2/tournaments/{id}    |
| api_v2_tournaments_U    | PUT    | /api/v2/tournaments/{id}    |
| api_v2_tournaments_D    | DELETE | /api/v2/tournaments/{id}    |
| api_v2_tournaments_L    | GET    | /api/v2/tournaments         |
| api_v2_tournaments_P    | PATCH  | /api/v2/tournaments/{id}    |

#### Routes generated for `categories`:

| Route Name              | Method | Path                                                      |
|-------------------------|--------|-----------------------------------------------------------|
| api_v2_categories_C     | POST   | /api/v2/tournaments/{tournament}/categories               |
| api_v2_categories_R     | GET    | /api/v2/tournaments/{tournament}/categories/{id}          |
| api_v2_categories_U     | PUT    | /api/v2/tournaments/{tournament}/categories/{id}          |
| api_v2_categories_D     | DELETE | /api/v2/tournaments/{tournament}/categories/{id}          |
| api_v2_categories_L     | GET    | /api/v2/tournaments/{tournament}/categories               |
| api_v2_categories_P     | PATCH  | /api/v2/tournaments/{tournament}/categories/{id}          |

> For versions below `v2`, no version is included in the route path, e.g. `/api/categories/{id}`.

---

## 📘 How It Works

- Routes are auto-generated from YAML config and handled by a central `CrudEventController`
- Symfony events are dispatched **before** and **after** each operation
- Incoming data is deserialized and validated using **validation groups**:
  - `fromJson()` accepts validation groups that control which fields are deserialized
  - `validate()` runs validation based on those groups
- Validation groups can be set using PHP attributes/annotations:

```php
#[Assert\NotBlank(groups: ['create'])]
#[Groups(['create', 'update'])]
private string $name;
```

This allows flexible validation rules depending on the operation.

---

## 🧩 EventListeners

You can register event listeners to:

- Log actions  
- Send notifications  
- Modify or enrich data  
- Handle errors  

---

## 📄 Pagination, Sorting, Filtering

### Pagination Parameters

| Parameter   | Default |
|-------------|---------|
| `page`      | 1       |
| `pageSize`  | 25      |

Example:
```
GET /api/v2/tournaments?page=2&pageSize=10
```

### 🔃 Sorting

- `sort=+field1,-field2` — ascending/descending  
- Example:
```
GET /api/v2/tournaments?sort=-age,+name
```

### 🧰 Filtering

- `filter[field]=value`
- `filter[field][operator]=value`

Supported operators:

| Operator | Description         |
|----------|---------------------|
| eq       | equals (default)    |
| like     | substring match     |
| gte      | greater or equal    |
| lte      | less or equal       |
| gt       | greater than        |
| lt       | less than           |

Boolean values supported: `true`, `false`, `none`

Examples:

```http
GET /api/v2/tournaments?filter[isActive]=true
GET /api/v2/tournaments?filter[status][eq]=active
GET /api/v2/tournaments?filter[rating][gte]=3&filter[rating][lte]=5
```

---

## 📦 Nested Resources

If a parent ID (e.g., UUID) is present in the path (e.g., `/api/v2/tournaments/{tournament}/categories`), it is:

- Automatically resolved and injected
- Available for filtering

You can combine all parameters:

```
GET /api/v2/tournaments/{tournament}/categories?page=2&pageSize=10&sort=-age,+name&filter[rating][gte]=3&filter[rating][lte]=5
```

---

## 📢 Dispatched Events

### Create
- `{resource}.create.before_persist`
- `crud_event.create.before_persist`
- `{resource}.create.after_persist`
- `crud_event.create.after_persist`

### Update
- `{resource}.update.before_persist`
- `crud_event.update.before_persist`
- `{resource}.update.after_persist`
- `crud_event.update.after_persist`

### Patch
- `{resource}.patch.before_persist`
- `crud_event.patch.before_persist`
- `{resource}.patch.after_persist`
- `crud_event.patch.after_persist`

### Delete
- `{resource}.delete.before_remove`
- `crud_event.delete.before_remove`
- `{resource}.delete.after_remove`
- `crud_event.delete.after_remove`

### List
- `{resource}.list.list_settings`
- `crud_event.list.filter_build`

---

### 🔔 Example Event Listener Registration

```yaml
App\EventListener\TournamentCrudListener:
    tags:
        - { name: kernel.event_listener, event: 'tournaments.create.after_persist', method: onAfterPersist }
```

---

## ❗ Custom Exceptions

- `PayloadValidationException`  
  Thrown when validation fails; includes `ConstraintViolationListInterface` for detailed violation info.

- `ResourceNotFoundException`  
  Thrown when the requested resource is not found by ID.

You can register listeners to handle these exceptions globally.
