# GLPI AutoAssign

Automatic technician assignment for GLPI 12.

## Purpose

The plugin is designed for this workflow:

1. A user creates a ticket and selects a category.
2. Existing GLPI Business Rules assign a technician group to the ticket.
3. AutoAssign reads the group assigned to the ticket.
4. It finds active users belonging to that group.
5. It assigns one of them using either **Least Loaded** or **Round-Robin**.

The plugin does not choose from a global technician list: the ticket's assigned group is the source of candidates.

## Features in 0.1.0

- GLPI 12 target.
- Least-loaded assignment.
- Round-Robin assignment.
- Optional maximum number of active tickets per technician.
- Optional inclusion of waiting tickets in workload.
- Option not to replace an already assigned technician.
- Configuration page.

## Install for development

Clone the repository into the GLPI plugins directory:

```bash
git clone https://github.com/Shkila25/glpi-autoassign.git <GLPI>/plugins/glpi_autoassign
```

Then install and enable **GLPI AutoAssign** in GLPI.

## Important

Test on a development GLPI instance first. Back up the database before testing on production and disable outbound notifications during development.

## License

GPL-3.0-or-later
