import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ProjectSelectComponent } from './project-select.component';
import { ProjectSelectRoutingModule } from './project-select-routing.module';

@NgModule({
  imports: [CommonModule, ProjectSelectRoutingModule],
  declarations: [ProjectSelectComponent],
  exports: [ProjectSelectComponent]
})
export class ProjectSelectModule { }
