import { Component, OnInit } from '@angular/core';
import { ProjectService } from '../shared/services/project.service';

@Component({
  moduleId: module.id,
  selector: 'sd-test-services',
  templateUrl: 'test-services.component.html'
})
export class TestServicesComponent implements OnInit {


  constructor(public projectService: ProjectService) {}

  projects: any[] = [];
  errorMessage: string;

  ngOnInit() {
    this.getProjects();
  }

  getProjects() {
    this.projectService.get()
      .subscribe(
        projects => this.projects = projects,
        error => this.errorMessage = <any>error
      );
  }

}
