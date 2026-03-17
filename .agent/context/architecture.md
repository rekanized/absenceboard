# Architecture & Data Model

## Data Model
The applications uses a relational model to track structure and events.

### Entities
- **Department**: Organizational units (e.g., Engineering, Operations, Sales).
- **User**: Individual employees, assigned to a Department and a specific location (Site).
- **Absence**: Specific date-based events for a User (Type: S=Semester, FL=Parental, B=Unassigned).
- **Holiday**: Global public holidays that affect the planner grid.

## Data Relationships
- `Department` has many `Users`.
- `User` belongs to a `Department`.
- `User` has many `Absences`.
- `Absence` belongs to a `User`.

## Key Logic
- **Grid Generation**: Days are generated using `CarbonPeriod` and decorated with holiday/weekend metadata.
- **Filtering**: Site filtering is performed via Eloquent eager loading constraints in `VacationPlanner::render()`.
- **Persistence**: Absences are managed via `Absence::updateOrCreate` for efficiency during range updates.
- **UI State**: Interactive drag selection is managed via Alpine.js on the frontend and synced with Livewire.
