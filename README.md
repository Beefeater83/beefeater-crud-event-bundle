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
crud_api_v1:
    resource: '%kernel.project_dir%/config/crud_routes_v1.yaml'
    type: crud_routes
```

### Example: `crud_routes_v1.yaml`

```yaml
version: v1
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

| Route Name           | Method | Path                     |
|----------------------|--------|--------------------------|
| api_v1_tournaments_C | POST   | /api/v1/tournaments      |
| api_v1_tournaments_R | GET    | /api/v1/tournaments/{id} |
| api_v1_tournaments_U | PUT    | /api/v1/tournaments/{id} |
| api_v1_tournaments_D | DELETE | /api/v1/tournaments/{id} |
| api_v1_tournaments_L | GET    | /api/v1/tournaments      |
| api_v1_tournaments_P | PATCH  | /api/v1/tournaments/{id} |

#### Routes generated for `categories`:

| Route Name          | Method | Path                                             |
|---------------------|--------|--------------------------------------------------|
| api_v1_categories_C | POST   | /api/v1/tournaments/{tournament}/categories      |
| api_v1_categories_R | GET    | /api/v1/tournaments/{tournament}/categories/{id} |
| api_v1_categories_U | PUT    | /api/v1/tournaments/{tournament}/categories/{id} |
| api_v1_categories_D | DELETE | /api/v1/tournaments/{tournament}/categories/{id} |
| api_v1_categories_L | GET    | /api/v1/tournaments/{tournament}/categories      |
| api_v1_categories_P | PATCH  | /api/v1/tournaments/{tournament}/categories/{id} |

> ⚠️ If the ***`version:`*** key is **not specified** in the configuration file (e.g. `crud_routes_v1.yaml`), the route paths will be built **without any version prefix**, for example: `/api/categories/{id}`.

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
GET /api/v1/tournaments?page=2&pageSize=10
```

### 🔃 Sorting

- `sort=+field1,-field2` — ascending/descending  
- Example:
```
GET /api/v1/tournaments?sort=-age,+name
```

### 🧰 Filtering

- `filter[field]=value`
- `filter[field][operator]=value`

Supported operators:

| Operator | Description                       |
|----------|-----------------------------------|
| eq       | equals (default) (null safe)      |
| like     | substring match                   |
| gte      | greater or equal                  |
| lte      | less or equal                     |
| gt       | greater than                      |
| lt       | less than                         |
| neq      | NOT equals (null safe)            |
| in       | value is in array (null safe)     |
| nin      | value is NOT in array (null safe) |
Boolean values supported: `true`, `false`, `none`

Examples:

```http
GET /api/v1/tournaments?filter[isActive]=true
GET /api/v1/tournaments?filter[status][eq]=active
GET /api/v1/tournaments?filter[rating][gte]=3&filter[rating][lte]=5
GET /api/v1/tournaments?filter[category][in][]=pro&filter[category][nin][]=amateur
```

---

## 📦 Nested Resources

If a parent ID (e.g., UUID) is present in the path (e.g., `/api/v1/tournaments/{tournament}/categories`), it is:

- Automatically resolved and injected
- Available for filtering

You can combine all parameters:

```
GET /api/v1/tournaments/{tournament}/categories?page=2&pageSize=10&sort=-age,+name&filter[rating][gte]=3&filter[rating][lte]=5
```

---

## 📢 Dispatched Events

### Create
- `{resource}.create.on_request`
- `{resource}.create.before_persist`
- `crud_event.create.before_persist`
- `{resource}.create.after_persist`
- `crud_event.create.after_persist`

### Update
- `{resource}.update.on_request`
- `{resource}.update.before_persist`
- `crud_event.update.before_persist`
- `{resource}.update.after_persist`
- `crud_event.update.after_persist`

### Patch
- `{resource}.patch.on_request`
- `{resource}.patch.before_persist`
- `crud_event.patch.before_persist`
- `{resource}.patch.after_persist`
- `crud_event.patch.after_persist`

### Delete
- `{resource}.delete.on_request`
- `{resource}.delete.before_remove`
- `crud_event.delete.before_remove`
- `{resource}.delete.after_remove`
- `crud_event.delete.after_remove`

### List
- `{resource}.list.list_settings`
- `crud_event.list.filter_build`

### Before deserialize
- `entity.before_deserialize`

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

## 📝 Logging

The Beefeater CRUD Event Bundle supports logging of key operations such as:

- Route creation
- Error logging
- Warning logging

### How to Enable Logging in Your Project

To enable logging for this bundle, follow these steps:

1. Install the Symfony Monolog Bundle if you haven’t already:

```bash
composer require symfony/monolog-bundle
```

2. Configure a dedicated logging channel and handler for `crud_event` in your `config/packages/monolog.yaml` file. For example, in the `dev` environment:

```yaml
monolog:
  channels:
    - crud_event # add this channel alongside your existing ones
    
when@dev:
    monolog:
        handlers:
          crud_event: # add this handler alongside your existing ones
                type: stream
                path: "%kernel.logs_dir%/crud_event.log"
                level: debug
                channels: ["crud_event"]
```

3. You can similarly add configurations for `when@test` just change log file path `%kernel.logs_dir%/crud_event_test.log`.
   For the `when@prod` environment, it's recommended to keep the default logging setup using the `fingers_crossed` handler

---

All logs related to the Beefeater CRUD Event Bundle will be saved in:

```
var/log/crud_event.log
```

This setup allows you to conveniently monitor important bundle actions and errors separately from other Symfony logs.

## 🧩 Controller Inheritance

CrudEventController exposes protected handle* methods for reuse in child controllers

You can define your own endpoints, run **custom logic (e.g. SECURITY checks)**, and then call:
- parent::handleCreate(Request \$request, string \$resourceName, string \$entityClass, ?string \$version = null)
- parent::handleUpdate(Request \$request, string \$resourceName, string \$entityClass, string \$id, ?string \$version = null)
- parent::handlePatch(Request \$request, string \$resourceName, string \$entityClass, string \$id, ?string \$version = null)
- parent::handleDelete(Request \$request, string \$resourceName, string \$id, ?string \$version = null)
- parent::handleList(Request \$request, Page \$page, Sort \$sort, Filter \$filter, string \$resourceName)

⚠️ Note: When using handleList() in your child controllers, make sure to import the types from the bundle:
- use Beefeater\CrudEventBundle\Model\Page;
- use Beefeater\CrudEventBundle\Model\Sort;
- use Beefeater\CrudEventBundle\Model\Filter;
