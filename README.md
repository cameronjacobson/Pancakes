# Pancakes

When you eat pancakes, try to visualize the both serial, and parallel execution of that task.

You walk to the table, sit down (serial), consume the stack of pancakes (parallel), then walk away from the table (back to serial).

I generally encounter a very small but important problem regularly when developing web applications:

 * Application receives a request
 * Application needs to load many resources, from possibly many data stores, to handle that request
 * Application sends a response.

I encounter this problem frequently enough that I need a standardized solution to it, but doesnt (in many cases) warrant a large often-times server-sized solution.

If I can toggle back and forth between serial and concurrent in a straightforward way throughout the workflow of my request/response cycle, it will be a great first step toward some modest performance gains in my projects requiring minimal effort to deploy.

## see examples in:

examples/*

