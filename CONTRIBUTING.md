In developing this project, follow the minimalist principle.
This is only a layer between Nginx and destination file, and it should not be large.
When connecting external dependencies, check that they do not pull for a
megabytes of unnecessary code.
Avoid excessive abstraction.
Reduce the number of inspections to obtain the final result for each request.