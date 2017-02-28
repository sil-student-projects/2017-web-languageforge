import { Component, OnInit, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';

import { ProjectService } from '../shared/services/project.service';

import { MaterializeDirective, MaterializeAction } from 'angular2-materialize';
declare var Materialize: any;

@Component({
  moduleId: module.id,
  selector: 'dashboard',
  templateUrl: 'dashboard.component.html'
})

export class DashboardComponent {

  joinedProjects: any[];
  allProjects: any[];
  showProjects: boolean = false;
  filterText: string = '';

  constructor(private projectService: ProjectService, 
              private router: Router) { }

  ngOnInit(): void {
    this.getJoinedProjects();
  }

  getJoinedProjects() {
    this.projectService.getJoinedProjectList().subscribe(projects => {
      console.log(projects);
      this.joinedProjects = projects.entries;
    });
  }

  getAllProjects() {
    this.projectService.getAllProjectList().subscribe(projects => {
      this.allProjects = projects.entries;
    });
  }

  onSelect(project: any) {
    this.projectService.setProjectId(project.id);
    this.router.navigate(['/review', project.id]);
  }

  joinProject(project: any) {
    if (this.isProjectJoined(project)) {
      Materialize.toast("<b>You have already joined this project!</b>", 2000, 'red');
    } else {
      this.projectService.joinProject(project.id).subscribe(response => {
        if (response.success) {
          this.joinedProjects.push(project);
          Materialize.toast("<b>Project joined!</b>", 2000, 'green');
        } else {
          Materialize.toast("<b>Error while joining project.</b>", 2000, 'red');
        }
      })
    }
  }

  private isProjectJoined(project: any) {
    for (let i = 0; i < this.joinedProjects.length; i++) {
      if (this.joinedProjects[i].id == project.id) {
        return true;
      }
    }
    return false;
  }

  showAllProjects(showProjects: boolean) {
    if (showProjects) {
      this.getAllProjects();
    }
    this.showProjects = showProjects;
  }
}
