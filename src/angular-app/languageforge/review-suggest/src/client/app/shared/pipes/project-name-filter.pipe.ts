import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'projectNameFilter',
    pure: false
})
export class ProjectNameFilterPipe implements PipeTransform {
    transform(projects: any[], args: any[]): any {
        // filter projects array, projects which match and return true will be kept, false will be filtered out
        return projects.filter(project => project.projectName.indexOf(args) !== -1);
    }
}