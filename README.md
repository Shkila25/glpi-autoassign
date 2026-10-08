# GLPI AutoAssign

Automatic technician assignment plugin for GLPI 12.

Workflow: ticket category -> existing GLPI Business Rule -> assigned group -> AutoAssign -> eligible technician -> assignment.

Modes:
- Least loaded
- Round-robin with persistent database state

Options:
- maximum active tickets per technician
- include Waiting tickets
- never replace an existing technician

Install by extracting the release ZIP into `<GLPI>/plugins/`, producing `<GLPI>/plugins/glpiautoassign/`.

Test on a development GLPI instance before production use.
