import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { ProjectSelectComponent } from './project-select.component';

@NgModule({
  imports: [
    RouterModule.forChild([
      { path: 'project-select', component: ProjectSelectComponent }
    ])
  ],
  exports: [RouterModule]
})
export class ProjectSelectRoutingModule { }
